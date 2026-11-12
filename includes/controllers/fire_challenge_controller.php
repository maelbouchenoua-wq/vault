<?php
$libraryDir = dirname(__DIR__);
if (is_file($libraryDir . '/libs/auth.php')) {
    $libraryDir .= '/libs';
}
require_once $libraryDir . '/auth.php';
require_once $libraryDir . '/db.php';

// Le temps vient uniquement du serveur.
function fireChallengeNow(): float
{
    return microtime(true);
}

function fireChallengeRememberCancellation(string $clientId): void
{
    $cancelled = $_SESSION['fire_challenge_cancelled'] ?? [];
    if (!is_array($cancelled)) {
        $cancelled = [];
    }
    $cancelled[$clientId] = true;
    while (count($cancelled) > 32) {
        foreach ($cancelled as $oldestId => $value) {
            unset($cancelled[$oldestId]);
            break;
        }
    }
    $_SESSION['fire_challenge_cancelled'] = $cancelled;
}

function fireChallengeSuccess(array $run, ?array $user): array
{
    $data = [
        'ok' => true,
        'state' => $run['state'],
        'client_id' => $run['client_id'],
        'duration_ms' => 10000,
        'score' => $run['score'],
        'can_save' => false,
        'saved' => $run['state'] === 'saved',
        'cancelled' => $run['state'] === 'cancelled',
        'login_url' => authUrl('login.php'),
    ];
    if (isset($run['run_id'])) {
        $data['run_id'] = $run['run_id'];
    }
    if ($user !== null && $run['state'] === 'finished' && $run['user_id'] === $user['id']) {
        $data['can_save'] = true;
    }
    if (isset($run['best_score'])) {
        $data['best_score'] = $run['best_score'];
    }
    return ['status' => 200, 'data' => $data];
}

function fireChallengeFailure(int $status, string $code, string $message): array
{
    return ['status' => $status, 'data' => ['ok' => false, 'code' => $code, 'message' => $message]];
}

// Une annulation tardive ne doit pas viser une nouvelle partie.
function fireChallengeHandle(string $action, array $input, ?array $user): array
{
    $userId = 0;
    if ($user !== null) {
        $userId = $user['id'];
    }
    $run = $_SESSION['fire_challenge_run'] ?? null;
    if (!is_array($run)) {
        $run = null;
    }

    if ($action === 'start' || $action === 'cancel') {
        $clientId = $input['client_id'] ?? null;
        if (!is_string($clientId) || !preg_match('/^[A-Za-z0-9_-]{16,80}$/D', $clientId)) {
            return fireChallengeFailure(400, 'invalid_client_id', 'Identifiant de tentative invalide.');
        }
    }

    if ($action === 'start') {
        $cancelled = $_SESSION['fire_challenge_cancelled'] ?? [];
        if (is_array($cancelled) && isset($cancelled[$clientId])) {
            return fireChallengeFailure(409, 'run_cancelled', 'Cette tentative a été annulée.');
        }

        $now = fireChallengeNow();
        if ($run !== null && $run['state'] === 'active') {
            $elapsed = $now - $run['started_at'];
            if ($run['user_id'] !== $userId || $elapsed < 0 || $elapsed > 120) {
                $run['state'] = 'cancelled';
                fireChallengeRememberCancellation($run['client_id']);
                $_SESSION['fire_challenge_run'] = $run;
            } elseif ($run['client_id'] === $clientId) {
                return fireChallengeSuccess($run, $user);
            } else {
                return fireChallengeFailure(409, 'run_active', 'Une partie est déjà en cours.');
            }
        }

        if ($run !== null && $run['client_id'] === $clientId) {
            if ($run['state'] === 'cancelled' || $run['user_id'] !== $userId) {
                return fireChallengeFailure(409, 'run_cancelled', 'Cette tentative n’est plus disponible.');
            }
            return fireChallengeSuccess($run, $user);
        }

        if ($run !== null && $run['state'] === 'finished') {
            fireChallengeRememberCancellation($run['client_id']);
        }
        $run = [
            'run_id' => bin2hex(random_bytes(16)),
            'client_id' => $clientId,
            'user_id' => $userId,
            'state' => 'active',
            'score' => 0,
            'started_at' => $now,
        ];
        $_SESSION['fire_challenge_run'] = $run;
        return fireChallengeSuccess($run, $user);
    }

    if ($action === 'cancel') {
        $runId = $input['run_id'] ?? null;
        if ($runId !== null && (!is_string($runId) || !preg_match('/^[a-f0-9]{32}$/D', $runId))) {
            return fireChallengeFailure(400, 'invalid_run_id', 'Identifiant de partie invalide.');
        }
        if ($run !== null && $run['client_id'] === $clientId) {
            if ($runId !== null && $runId !== $run['run_id']) {
                return fireChallengeFailure(409, 'stale_run', 'Cette partie n’est plus la partie courante.');
            }
            if ($run['user_id'] !== $userId) {
                if ($run['state'] !== 'saved') {
                    $run['state'] = 'cancelled';
                    fireChallengeRememberCancellation($clientId);
                    $_SESSION['fire_challenge_run'] = $run;
                }
                return fireChallengeFailure(409, 'identity_changed', 'La connexion a changé. Recommencez une partie.');
            }
            if ($run['state'] === 'saved') {
                return fireChallengeSuccess($run, $user);
            }
            $run['state'] = 'cancelled';
            fireChallengeRememberCancellation($clientId);
            $_SESSION['fire_challenge_run'] = $run;
            return fireChallengeSuccess($run, $user);
        }
        if ($runId !== null) {
            return fireChallengeFailure(409, 'stale_run', 'Cette partie n’est plus la partie courante.');
        }
        fireChallengeRememberCancellation($clientId);
        return fireChallengeSuccess(['state' => 'cancelled', 'client_id' => $clientId, 'score' => 0], $user);
    }

    if ($action !== 'finish' && $action !== 'save') {
        return fireChallengeFailure(400, 'invalid_action', 'Action du défi inconnue.');
    }
    $runId = $input['run_id'] ?? null;
    if (!is_string($runId) || !preg_match('/^[a-f0-9]{32}$/D', $runId)) {
        return fireChallengeFailure(400, 'invalid_run_id', 'Identifiant de partie invalide.');
    }
    if ($run === null || $run['run_id'] !== $runId) {
        return fireChallengeFailure(409, 'stale_run', 'Cette partie n’est plus la partie courante.');
    }
    if ($run['user_id'] !== $userId) {
        if ($run['state'] !== 'saved') {
            $run['state'] = 'cancelled';
            fireChallengeRememberCancellation($run['client_id']);
            $_SESSION['fire_challenge_run'] = $run;
        }
        return fireChallengeFailure(409, 'identity_changed', 'La connexion a changé. Recommencez une partie.');
    }
    if ($run['state'] === 'cancelled') {
        return fireChallengeFailure(409, 'run_cancelled', 'Une partie annulée ne peut pas être enregistrée.');
    }

    if ($action === 'finish') {
        $score = $input['score'] ?? null;
        if (!is_int($score) || $score < 0 || $score > 1000) {
            return fireChallengeFailure(400, 'invalid_score', 'Le score doit être un entier entre 0 et 1000.');
        }
        if ($run['state'] === 'finished' || $run['state'] === 'saved') {
            if ($score !== $run['score']) {
                return fireChallengeFailure(409, 'score_locked', 'Le score de cette partie est déjà fixé.');
            }
            return fireChallengeSuccess($run, $user);
        }

        $elapsed = fireChallengeNow() - $run['started_at'];
        if ($elapsed < 0 || $elapsed > 120) {
            $run['state'] = 'cancelled';
            fireChallengeRememberCancellation($run['client_id']);
            $_SESSION['fire_challenge_run'] = $run;
            return fireChallengeFailure(409, 'run_expired', 'Cette partie a expiré. Recommencez.');
        }
        if ($elapsed < 10) {
            $result = fireChallengeFailure(409, 'too_early', 'La partie dure dix secondes.');
            $result['data']['remaining_ms'] = (int) ceil((10 - $elapsed) * 1000);
            return $result;
        }
        $run['state'] = 'finished';
        $run['score'] = $score;
        $_SESSION['fire_challenge_run'] = $run;
        return fireChallengeSuccess($run, $user);
    }

    if ($user === null) {
        return fireChallengeFailure(401, 'login_required', 'Connectez-vous pour enregistrer vos prochains scores.');
    }
    if ($run['state'] === 'saved') {
        return fireChallengeSuccess($run, $user);
    }
    if ($run['state'] !== 'finished') {
        return fireChallengeFailure(409, 'run_not_finished', 'Terminez la partie avant de l’enregistrer.');
    }

    // Le score enregistré vient de la partie, jamais du formulaire save.
    $pdo = getPDO(true);
    try {
        $pdo->beginTransaction();
        $query = $pdo->prepare(
            'UPDATE users SET best_score = CASE WHEN best_score < :score THEN :new_score ELSE best_score END WHERE id = :user_id'
        );
        $query->execute(['score' => $run['score'], 'new_score' => $run['score'], 'user_id' => $userId]);
        $query = $pdo->prepare('SELECT best_score FROM users WHERE id = :user_id');
        $query->execute(['user_id' => $userId]);
        $bestScore = $query->fetchColumn();
        if ($bestScore === false) {
            $pdo->rollBack();
            $run['state'] = 'cancelled';
            fireChallengeRememberCancellation($run['client_id']);
            $_SESSION['fire_challenge_run'] = $run;
            return fireChallengeFailure(403, 'account_unavailable', 'Ce compte ne peut plus enregistrer de score.');
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
    $run['state'] = 'saved';
    $run['best_score'] = (int) $bestScore;
    $_SESSION['fire_challenge_run'] = $run;
    return fireChallengeSuccess($run, $user);
}
