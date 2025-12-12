<!DOCTYPE html>
<html lang="eu">

<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    <style>
        body {
            font-family: Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            margin: 0pt;
            color: #000000;
        }

        table {
            width: 100%;
            font-size: 9pt !important;
            border-collapse: collapse;
            margin-top: 5pt;
            margin-bottom: 5pt;
        }

        th,
        td {
            border: 0.5pt solid #000000;
            padding: 3pt;
            vertical-align: top;
        }

        th {
            font-weight: bold;
            text-align: left;
        }

        img {
            max-width: 119px;
            height: auto;
        }

        #footer {
            position: fixed;
            bottom: -20px;

            left: 0;
            right: 0;
            text-align: center;
            color: #7E7E7E;
            font-size: 10pt;
        }
    </style>
</head>

<body>
    <!-- En-tête -->
    <table style="width: 100%; border: none; margin-bottom: 5pt;">
        <tr>
            <th style="width: 50%; border: none; vertical-align: top;">
                <img src="data:image/jpg;base64,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"
                    alt="Logo Ikastola" style="width: 119px; height: 104px;">
                <p style="font-weight: bold; margin: 0pt 0;">Hiriondo Ikastola</p>
                <p style="margin: 0pt 0;">Polo etorbidea, 11 – <span style="color: #7E7E7E;">11 avenue du Polo</span></p>
                <p style="margin: 0pt 0;">64100 BAIONA – <span style="color: #7E7E7E;">BAYONNE</span></p>
                <p style="margin: 0pt 0; font-size : 9pt;"><a href="mailto:hiriondo.diruzaina@seaska.eus"
                        style="color: #000000; text-decoration: none;">hiriondo.diruzaina@seaska.eus</a></p>
            </th>

            <th style="width: 50%; border: none; vertical-align: top;">
                <div
                    style="background: #FFAD5D; border: 1.5pt solid #BE4F13; padding: 8pt; text-align: center; margin-bottom: 0pt;">
                    <p style="font-weight: bold; font-size: 14pt; margin: 0;">FAKTURA – <span
                            style="color: #7E7E7E;">FACTURE</span></p>
                </div>
                <p style="color: #AA4512; font-weight: bold; font-size: 11pt; text-align: center; margin: 0pt 0;">
                    FACTURE n° {{ $facture->idFacture }}</p>
                <p style="margin: 5pt 0;">Data – <span style="color: #7E7E7E;">Date</span> :
                    {{ $facture->dateC->format('Y.m.d') }}</p>
            </th>
        </tr>
    </table>

    <!-- Responsable -->
    <p style="font-weight: bold; text-align: center; margin: 15pt 0;">
        {{ $famille->utilisateurs()->first()->nom }} haurr(ar)en arduraduna(k)
        <br><span style="color: #7E7E7E;">Responsable(s) de(s) enfant(s)
            {{ $famille->utilisateurs()->first()->nom }}</span>
    </p>

    <p style="font-weight: bold; margin: 10pt 0;">
        Haur kopurua – <span style="color: #7E7E7E;">Nombre d'enfant(s)</span> :
        <span style="color: #4BA22C;">{{ $enfants->count() }}</span>
    </p>

    <!-- Tableau des détails -->
    <table>
        <thead>
            <tr style="justify-content: center;">
                <th style="background: #8ED773; width: 40%;justify-content: center;  text-align: center;">Gaia <br />
                    <span style="color: #7E7E7E;">Désignation</span>
                </th>
                <th style="background: #C0E9B0; width: 30%; text-align: center;">BEZ gabeko prezioa <br /> <span
                        style="color: #7E7E7E;">Prix
                        unitaire HT</span></th>
                <th style="background: #C0E9B0; width: 30%; text-align: center;">BEZ gabeko orotara <br /> <span
                        style="color: #7E7E7E;">Total
                        HT</span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Oinarrizko kotizazioa <span style="color: #7E7E7E;">Cotisation de base</span></td>
                <td style="text-align: right;">{{ $montantcotisation ?? 0 }} € - eusko</td>
                <td style="text-align: right;">{{ $montantcotisation ?? 0 }} € - eusko</td>
            </tr>
            <tr>
                <td>Ateraldi, krakada eta horniduretarako parte-hartzea <span style="color: #7E7E7E;">Participation pour
                        sorties, goûters, fournitures</span></td>
                <td style="text-align: right;">9,65 € - eusko / haurka – <span style="color: #7E7E7E;">enfant</span>
                </td>
                <td style="text-align: right;">{{ number_format($montantparticipation ?? 0, 2, ',', '') }} € - eusko
                </td>
            </tr>
            <tr style="align-items: center;">
                <td>Seaska egutegiak, Herri Urrats txartelak <span style="color: #7E7E7E;">Calendrier Seaska, tickets
                        Herri Urrats</span></td>
                <td style="text-align: right;">7,70 € / eusko</td>
                <td style="text-align: right;">{{ number_format($montantparticipationSeaska ?? 0, 2, ',', '') }} € -
                    eusko</td>
            </tr>
            <tr>
                <td style="font-size: 9pt;">Aurreikusi haurtzaindegia (3. haurrarentzat, urririk)
                    <span style="color: #7E7E7E;"> garderie (gratuit pour le 3<sup>e</sup> enfant)</span>
                </td>
                <td style="font-size: 9pt; text-align: right;">1 - 8 aldiz – <span style="color: #7E7E7E;">fois</span> :
                    10 € - eusko /
                    haurka – <span style="color: #7E7E7E;">enfant</span><br>
                    + 9 aldiz – <span style="color: #7E7E7E;">fois</span> : 20 € - eusko / haurka – <span
                        style="color: #7E7E7E;">enfant</span></td>
                <td style="text-align: right;">
                    @if ($facture->previsionnel)
                        <span style="font-size: 9pt;">montant previsionnel</span>
                    @endif
                    {{ $montangarderie ?? 0 }} € - eusko
                </td>
            </tr>
            <tr>
                <td style="border: 0px"></td>
                <td style="background: #8ED773; font-weight: bold; text-align: center;">Zure gain den BEZekin
                    Orotara<br /> <span style="color: #7E7E7E;">Total TTC à votre charge</span></td>
                <td style="background: #8ED773; font-weight: bold; text-align: center;">
                    {{ number_format($montanttotal ?? 0, 2, ',', '') }} € - eusko</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size: 9pt; margin: 0pt 0;">BEZ-a ez aplikagarria, ZKN-aren 293B artikulua <br><span
            style="color: #7E7E7E;">TVA non applicable, article 293B du CGI</span></p>

    <!-- Montant mensuel -->
    <p style="font-weight: bold; margin-top: 5pt; margin-bottom: 0pt;">Hileko zenbatekoa:</p>
    <p style="color: #7E7E7E; margin-bottom: 0pt;">Montant prélevé mensuellement :</p>

    <table style="width: 100%;">
        <tr>
            <th style="width: 15%; text-align: center; padding: 4pt;">2025.09.13</th>
            <th style="width: 32%; text-align: center; padding: 4pt;"> {{$totalPrevisionnel}} € - eusko</th>
            <th style="width: 6%; border: none;"></th>
            <th style="width: 15%; text-align: center; padding: 4pt;">2026.03.13</th>
            <th style="width: 32%; text-align: center; padding: 4pt;"> {{$totalPrevisionnel}} € - eusko</th>
        </tr>
        <tr>
            <td style="text-align: center; padding: 4pt;">2025.10.13</td>
            <td style="text-align: center; padding: 4pt;">{{$totalPrevisionnel}} € - eusko</td>
            <td style="border: none;"></td>
            <td style="text-align: center; padding: 4pt;">2026.04.13</td>
            <td style="text-align: center; padding: 4pt;">{{$totalPrevisionnel}} € - eusko</td>
        </tr>
        <tr>
            <td style="text-align: center; padding: 4pt;">2025.11.13</td>
            <td style="text-align: center; padding: 4pt;">{{$totalPrevisionnel}} € - eusko</td>
            <td style="border: none;"></td>
            <td style="text-align: center; padding: 4pt;">2026.05.13</td>
            <td style="text-align: center; padding: 4pt;">{{$totalPrevisionnel}} € - eusko</td>
        </tr>
        <tr>
            <td style="text-align: center; padding: 4pt;">2025.12.13</td>
            <td style="text-align: center; padding: 4pt;">{{$totalPrevisionnel}} € - eusko</td>
            <td style="border: none;"></td>
            <td style="text-align: center; padding: 4pt;">2026.06.13</td>
            <td style="text-align: center; padding: 4pt;">{{$totalPrevisionnel}} € - eusko</td>
        </tr>
        <tr>
            <td style="text-align: center; padding: 4pt;">2026.01.13</td>
            <td style="text-align: center; padding: 4pt;">{{$totalPrevisionnel}} € - eusko</td>
            <td style="border: none;"></td>
            <td style="text-align: center; padding: 4pt;">2026.07.13</td>
            <td style="text-align: center; padding: 4pt;">{{$totalPrevisionnel}} € - eusko</td>
        </tr>
        <tr>
            <td style="text-align: center; padding: 4pt; color: #974705;">2026.02.13</td>
            <td style="text-align: center; padding: 4pt;">
                {{$totalPrevisionnel}} € - eusko<br>
                <span style="font-size: 9pt;">+/- <span style="color: #AA4512;">ERREGULARTZEA</span> – <span
                        style="color: #ED8D5C;">REGULARISATION</span></span>
            </td>
            <td style="border: none;"></td>
            <td style="text-align: center; padding: 4pt; color: #974705;">2026.08.13</td>
            <td style="text-align: center; padding: 4pt;">
                {{$totalPrevisionnel}} € - eusko<br>
                <span style="font-size: 9pt;">+/- <span style="color: #AA4512;">ERREGULARTZEA</span> – <span
                        style="color: #ED8D5C;">REGULARISATION</span></span>
            </td>
        </tr>
    </table>

    <!-- Conditions de paiement -->
    <p style="font-weight: bold; margin-top: 20pt; margin-bottom: 10pt;">Milesker zure kontuan diru aski duzula
        segurtatzeagatik. <span style="color: #7E7E7E;">Merci de bien vouloir en assurer la provision sur votre
            compte.</span></p>

    <p style="font-size: 8pt; margin-bottom: 8pt; text-align: justify;">
        Ordainketa-baldintzak: epea amaitzean ordainduko da. <span style="color: #7E7E7E;">Conditions de paiement :
            prélèvement à échéance.</span>
        Ez da deskonturik aplikatuko aintzinago ordaintzeagatik. <span style="color: #7E7E7E;">Aucun escompte consenti
            pour règlement anticipé.</span>
    </p>

    <p style="font-size: 8pt; margin-bottom: 8pt; text-align: justify;">
        Ordainketan izandako edozein gorabeherak interesak sortuko ditu. Penalizazioa kalkulatzeko, zor den diruaz gain,
        arazoa gertatu den unean indarrean dagoen interes tasa legala aplikatu da.
        <span style="color: #7E7E7E;">Tout incident de paiement est passible d'intérêt de retard. Le montant des
            pénalités résulte de l'application, aux sommes restant dues, du taux d'intérêt légal en vigueur au moment de
            l'incident.</span>
    </p>

    <p style="font-size: 8pt; text-align: justify;">
        Beranduegi ordainduz gero, zordunak kredituari ordaindu beharreko kalte-ordain finkoa: 40 €.
        <span style="color: #7E7E7E;">Indemnité forfaitaire pour frais de recouvrement due au créancier en cas de
            retard de paiement : 40€.</span>
    </p>
    <div id="footer">
        <hr style="margin-bottom: 5px;">
        <p>
            Association Loi 1901 enregistrée sous le N° 19800035 à la
            Sous-préfecture de Bayonne - N° SIRET : 32416580200020 Catégorie juridique : 9220 Association déclarée –
            hiriondo.bulegoa@seaska.eus
        </p>
    </div>
</body>

</html>
