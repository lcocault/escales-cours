<?php
// src/Mailer.php – email helpers (uses PHP mail() by default)

class Mailer
{
    private static function send(string $to, string $subject, string $bodyHtml): void
    {
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM . '>' . "\r\n";

        mail($to, $subject, $bodyHtml, $headers);
    }

    public static function sendBookingConfirmationToAttendee(
        array $user,
        array $session
    ): void {
        $subject = 'Confirmation de réservation – ' . $session['title'];
        $body    = self::bookingConfirmationBody($user, $session);
        self::send($user['email'], $subject, $body);
    }

    public static function sendBookingNotificationToAdmin(
        array $user,
        array $session
    ): void {
        $subject = '[Admin] Nouvelle réservation – ' . $session['title'];
        $body    = self::adminNotificationBody($user, $session);
        self::send(ADMIN_EMAIL, $subject, $body);
    }

    public static function sendCancellationConfirmationToAttendee(
        array $user,
        array $session
    ): void {
        $subject = 'Annulation de réservation – ' . $session['title'];
        $body    = self::cancellationConfirmationBody($user, $session);
        self::send($user['email'], $subject, $body);
    }

    public static function sendCancellationNotificationToAdmin(
        array $user,
        array $session
    ): void {
        $subject = '[Admin] Annulation de réservation – ' . $session['title'];
        $body    = self::adminCancellationBody($user, $session);
        self::send(ADMIN_EMAIL, $subject, $body);
    }

    public static function sendSessionConfirmationToAttendee(
        array $user,
        array $session
    ): void {
        $subject = 'Séance confirmée – ' . $session['title'];
        $body    = self::sessionConfirmationBody($user, $session);
        self::send($user['email'], $subject, $body);
    }

    public static function sendSessionCancellationToAttendee(
        array $user,
        array $session
    ): void {
        $subject = 'Séance annulée – ' . $session['title'];
        $body    = self::sessionCancellationBody($user, $session);
        self::send($user['email'], $subject, $body);
    }

    public static function sendPrivateSessionInvitation(
        array $user,
        array $session
    ): void {
        $subject = 'Invitation – ' . $session['title'];
        $body    = self::privateSessionInvitationBody($user, $session);
        self::send($user['email'], $subject, $body);
    }

    private static function sessionConfirmationBody(array $user, array $session): string
    {
        $name    = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
        $title   = htmlspecialchars($session['title']);
        $date    = htmlspecialchars($session['session_date']);
        $start   = htmlspecialchars($session['start_time']);
        $end     = htmlspecialchars($session['end_time']);
        $baseUrl = APP_BASE_URL;

        return <<<HTML
        <p>Bonjour {$name},</p>
        <p>Bonne nouvelle ! La séance <strong>{$title}</strong> est confirmée et aura bien lieu.</p>
        <p>📅 Date : {$date}<br>🕐 Horaires : {$start} – {$end}</p>
        <p>Nous avons hâte de vous accueillir !</p>
        <p>À bientôt aux Escales Culinaires !</p>
        <p><a href="{$baseUrl}/my-sessions.php">Voir mes réservations</a></p>
        HTML;
    }

    private static function sessionCancellationBody(array $user, array $session): string
    {
        $name    = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
        $title   = htmlspecialchars($session['title']);
        $date    = htmlspecialchars($session['session_date']);
        $start   = htmlspecialchars($session['start_time']);
        $end     = htmlspecialchars($session['end_time']);
        $baseUrl = APP_BASE_URL;

        return <<<HTML
        <p>Bonjour {$name},</p>
        <p>Nous sommes désolés de vous informer que la séance <strong>{$title}</strong> est annulée faute d'un nombre suffisant de participants.</p>
        <p>📅 Date : {$date}<br>🕐 Horaires : {$start} – {$end}</p>
        <p>Votre paiement sera intégralement remboursé sous quelques jours ouvrés.</p>
        <p>À bientôt aux Escales Culinaires !</p>
        <p><a href="{$baseUrl}/my-sessions.php">Voir mes réservations</a></p>
        HTML;
    }

    private static function bookingConfirmationBody(array $user, array $session): string
    {
        $name    = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
        $title   = htmlspecialchars($session['title']);
        $date    = htmlspecialchars($session['session_date']);
        $start   = htmlspecialchars($session['start_time']);
        $end     = htmlspecialchars($session['end_time']);
        $baseUrl = APP_BASE_URL;

        return <<<HTML
        <p>Bonjour {$name},</p>
        <p>Votre réservation pour la session <strong>{$title}</strong> est confirmée.</p>
        <p>📅 Date : {$date}<br>🕐 Horaires : {$start} – {$end}</p>
        <p>📍 L'atelier se déroule au :<br>
           <strong>Les Escales Culinaires</strong><br>
           36 rue Boieldieu, 31300 Toulouse</p>
        <p>⏰ Merci d'amener votre enfant <strong>10 minutes avant le début</strong> de la séance
           et de le récupérer dans les <strong>10 minutes suivant la fin</strong>.</p>
        <p>Vous recevrez le contenu détaillé après la session.</p>
        <p>À bientôt aux Escales Culinaires !</p>
        <p><a href="{$baseUrl}/my-sessions.php">Voir mes réservations</a></p>
        HTML;
    }

    private static function adminNotificationBody(array $user, array $session): string
    {
        $name    = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
        $email   = htmlspecialchars($user['email']);
        $title   = htmlspecialchars($session['title']);
        $date    = htmlspecialchars($session['session_date']);
        $baseUrl = APP_BASE_URL;

        return <<<HTML
        <p>Nouvelle réservation reçue :</p>
        <ul>
          <li>Session : <strong>{$title}</strong> ({$date})</li>
          <li>Participant : {$name} ({$email})</li>
        </ul>
        <p><a href="{$baseUrl}/admin/attendees.php?session_id={$session['id']}">Voir les participants</a></p>
        HTML;
    }

    private static function cancellationConfirmationBody(array $user, array $session): string
    {
        $name    = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
        $title   = htmlspecialchars($session['title']);
        $date    = htmlspecialchars($session['session_date']);
        $start   = htmlspecialchars($session['start_time']);
        $end     = htmlspecialchars($session['end_time']);
        $baseUrl = APP_BASE_URL;

        return <<<HTML
        <p>Bonjour {$name},</p>
        <p>Votre réservation pour la session <strong>{$title}</strong> a bien été annulée.</p>
        <p>📅 Date : {$date}<br>🕐 Horaires : {$start} – {$end}</p>
        <p>Le remboursement sera traité sous quelques jours ouvrés.</p>
        <p>À bientôt aux Escales Culinaires !</p>
        <p><a href="{$baseUrl}/my-sessions.php">Voir mes réservations</a></p>
        HTML;
    }

    private static function adminCancellationBody(array $user, array $session): string
    {
        $name    = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
        $email   = htmlspecialchars($user['email']);
        $title   = htmlspecialchars($session['title']);
        $date    = htmlspecialchars($session['session_date']);
        $baseUrl = APP_BASE_URL;

        return <<<HTML
        <p>Annulation de réservation reçue :</p>
        <ul>
          <li>Session : <strong>{$title}</strong> ({$date})</li>
          <li>Participant : {$name} ({$email})</li>
        </ul>
        <p><a href="{$baseUrl}/admin/attendees.php?session_id={$session['id']}">Voir les participants</a></p>
        HTML;
    }

    private static function privateSessionInvitationBody(array $user, array $session): string
    {
        $name    = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
        $title   = htmlspecialchars($session['title']);
        $date    = htmlspecialchars($session['session_date']);
        $start   = htmlspecialchars($session['start_time']);
        $end     = htmlspecialchars($session['end_time']);
        $baseUrl = APP_BASE_URL;
        $sessionUrl = $baseUrl . '/session.php?id=' . (int) $session['id'];

        return <<<HTML
        <p>Bonjour {$name},</p>
        <p>Vous avez été invité(e) à participer à la séance privée <strong>{$title}</strong>.</p>
        <p>📅 Date : {$date}<br>🕐 Horaires : {$start} – {$end}</p>
        <p>Pour réserver votre place, cliquez sur le lien ci-dessous :</p>
        <p><a href="{$sessionUrl}">Voir la séance et réserver</a></p>
        <p>À bientôt aux Escales Culinaires !</p>
        HTML;
    }
}
