<?php
include_once("../config/config.php");
include_once("../config/database.php");
require '../vendor/autoload.php'; // For PDF library

use Dompdf\Dompdf;

if (!isset($_POST['user_id'])) {
    die("Invalid request");
}

$user_id = $_POST['user_id'];
$start_date = $_POST['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? date('Y-m-t');

// Fetch data for the report
$query = "SELECT e.date, 'Expense' as type, c.category_name, e.amount, e.notes 
          FROM expenses e 
          JOIN categories c ON e.category_id = c.category_id 
          WHERE e.user_id = ? AND e.date BETWEEN ? AND ?
          UNION 
          SELECT i.date, 'Income' as type, c.category_name, i.amount, i.notes 
          FROM income i 
          JOIN categories c ON i.category_id = c.category_id 
          WHERE i.user_id = ? AND i.date BETWEEN ? AND ?
          ORDER BY date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ississ", $user_id, $start_date, $end_date, $user_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// Export to CSV
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="report.csv"');

    $output = fopen("php://output", "w");
    fputcsv($output, ["Date", "Type", "Category", "Amount", "Notes"]);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Export to PDF
if (isset($_POST['export_pdf'])) {
    $dompdf = new Dompdf();
    $html = '<h2 >Financial Report</h2>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>';
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr>
                    <td>{$row['date']}</td>
                    <td>{$row['type']}</td>
                    <td>{$row['category_name']}</td>
                    <td>Rs. " . number_format($row['amount'], 2) . "</td>
                    <td>{$row['notes']}</td>
                  </tr>";
    }
    $html .= '</tbody></table>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("report.pdf", ["Attachment" => 1]);
    exit();
}
?>
