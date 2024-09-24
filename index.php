<?php


// Definieer constanten
define('FILES_PATH', __DIR__ . '/transaction_files');  // Verander dit naar je eigen pad
define('VIEWS_PATH', __DIR__ . '/views');  // Dit wordt hier niet gebruikt, maar kan later nuttig zijn

// Laad bestanden in de directory
function getFiles($path) {

    $files = array_diff(scandir($path), array('.', '..'));
    return $files;
}

// Bestand lezen en parsen
function parseFile($filePath) {
    $data = [];
    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        $firstRow = true; // flag om de eerste regel te herkennen
        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if ($firstRow) {
                $firstRow = false; // Sla de eerste regel over
                continue; 
            }
            $data[] = $row;
        }
        fclose($handle);
    }
    return $data;
}


// Format bedragen
function formatAmount($amount) {
    if ($amount < 0) {
        return '<span class="text-danger">' . number_format($amount, 2, ',', '.') . '</span>';
    } else {
        return '<span class="text-success">' . number_format($amount, 2, ',', '.') . '</span>';
    }
}

// Verwerk de geselecteerde file
if (isset($_GET['file'])) {
    $fileName = $_GET['file'];
    $filePath = FILES_PATH . '/' . $fileName;

    if (file_exists($filePath)) {
        $data = parseFile($filePath);

        // Bereken totaal inkomen en uitgaven
        $totalIncome = 0;
        $totalExpenses = 0;

        // HTML output
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Data Viewer</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        </head>
        <body>
        <div class="container">
            <h1>Data Viewer</h1>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Checksum</th>
                        <th>Beschrijving</th>
                        <th>Bedrag</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($data as $row) {
            if (count($row) == 4) {
                $date = date('j F Y', strtotime($row[0]));
                $checksum = $row[1];
                $description = $row[2];
                $amount = (float) str_replace(',', '.', $row[3]);

                // Bereken totaal inkomen en uitgaven
                if ($amount > 0) {
                    $totalIncome += $amount;
                } else {
                    $totalExpenses += $amount;
                }

                echo '<tr>
                    <td>' . htmlspecialchars($date) . '</td>
                    <td>' . ($checksum ? htmlspecialchars($checksum) : '-') . '</td>
                    <td>' . htmlspecialchars($description) . '</td>
                    <td>' . formatAmount($amount) . '</td>
                </tr>';
            }
        }

        $netTotal = $totalIncome + $totalExpenses;

        echo '</tbody>
            </table>
            <h3>Totale inkomsten: ' . formatAmount($totalIncome) . '</h3>
            <h3>Totale uitgaven: ' . formatAmount($totalExpenses) . '</h3>
            <h3>Netto totaal: ' . formatAmount($netTotal) . '</h3>
        </div>
        </body>
        </html>';
    } else {
        echo 'Bestand niet gevonden.';
    }
} else {
    // Lijst van bestanden weergeven
    $files = getFiles(FILES_PATH);
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bestanden Lijst</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    </head>
    <body>
    <div class="container">
        <h1>Bestanden</h1>
        <ul class="list-group">';

    foreach ($files as $file) {
        echo '<li class="list-group-item">
            <a href="?file=' . urlencode($file) . '">' . htmlspecialchars($file) . '</a>
        </li>';
    }

    echo '</ul>
    </div>
    </body>
    </html>';
}
?>
