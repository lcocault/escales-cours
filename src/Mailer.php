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
}
