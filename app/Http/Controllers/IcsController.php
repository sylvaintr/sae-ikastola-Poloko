<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Evenement;
use Illuminate\Http\Response;

class IcsController extends Controller
{
    /**
     * Génère le flux ICS pour un utilisateur via son token.
     *
     * @param string $token Le token ICS unique de l'utilisateur.
     * @return Response
     */
    public function feed(string $token): Response
    {
        $user = Utilisateur::where('ics_token', $token)->first();

        if (!$user) {
            abort(404, 'Calendrier non trouvé');
        }

        $evenements = Evenement::all();

        $icsContent = $this->generateIcsContent($evenements);

        return response($icsContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="ikastola-poloko.ics"',
        ]);
    }

    /**
     * Génère le contenu au format iCalendar (RFC 5545).
     *
     * @param \Illuminate\Database\Eloquent\Collection $evenements
     * @return string
     */
    private function generateIcsContent($evenements): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Ikastola Poloko//Calendrier//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Ikastola Poloko',
        ];

        foreach ($evenements as $evenement) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:event-' . $evenement->idEvenement . '@ikastola-poloko';
            $lines[] = 'DTSTART:' . $this->formatDateForIcs($evenement->start_at);

            if ($evenement->end_at) {
                $lines[] = 'DTEND:' . $this->formatDateForIcs($evenement->end_at);
            }

            $lines[] = 'SUMMARY:' . $this->escapeIcsText($evenement->titre);

            if ($evenement->description) {
                $lines[] = 'DESCRIPTION:' . $this->escapeIcsText($evenement->description);
            }

            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines);
    }

    /**
     * Formate une date au format iCalendar (UTC).
     *
     * @param \Carbon\Carbon $date
     * @return string
     */
    private function formatDateForIcs($date): string
    {
        return $date->utc()->format('Ymd\THis\Z');
    }

    /**
     * Échappe les caractères spéciaux pour le format ICS.
     *
     * @param string $text
     * @return string
     */
    private function escapeIcsText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\,', $text);
        $text = str_replace(';', '\;', $text);
        $text = str_replace("\n", '\n', $text);
        $text = str_replace("\r", '', $text);
        return $text;
    }
}
