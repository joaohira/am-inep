<?php

$config = include('config.php');

if (!isset($argv[1])) {
    echo "Uso: php certificado.php (avaliacao|participacao)\n\n";
    exit();
}

$mode = $argv[1];

$modes_sql = $config['sql'];

if (!in_array($mode, array_keys($modes_sql))) {
    echo "Uso: php certificado.php (avaliacao|participacao)\n\n";
    exit();
}

require_once '../vendor/autoload.php';

// Caminho do arquivo de imagem
$img_dir = getcwd();
$img_file = $img_dir . '/' . $mode . '.jpg';

// Informações de salvamento
$save_dir = getcwd() . "/certificados/$mode/";
$sep = '_';
$file_name = 'certificado'. $sep . $mode;

$pdo = new PDO("mysql:dbname=$config['dbname'];host=$config['host'];charset=utf8", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$query = $pdo->prepare($modes_sql[$mode]);
$query->execute();

echo "\n" . $query->rowCount() . " arquivos serão gerados. Pressione enter para continuar... (Ctrl-C para sair)\n";
$handle = fopen ("php://stdin","r");
fgets($handle);
fclose($handle);

// Data do dia
// $date = new DateTime();
// $formatter = new IntlDateFormatter(
//    'pt_BR',
//     IntlDateFormatter::LONG,
//     IntlDateFormatter::NONE,
//     'America/Sao_Paulo',          
//     IntlDateFormatter::GREGORIAN
// );
// $date_string = $formatter->format($date);

while (($row = $query->fetch())) {
    $nome = $row['nome'];
    $email = $row['email'];

    // Criação do PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Informações de arquivo
    $pdf->SetCreator($config['author']);
    $pdf->SetAuthor($config['author']);
    $pdf->SetTitle('Certificado de Participação');
    $pdf->SetSubject($config['event_name']);

    // Configurações de margem
    $pdf->SetMargins(0, 0, 0, true);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(false, 0);

    // Escala de conversão de imagens
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Adicionar página
    $pdf->AddPage();

    // Adicionar imagem do fundo
    $pdf->Image($img_file, 0, 0, 0, 0, '', '', '', true, 300, '', false, false, 0, false, false, true);

    // Texto do título
    $pdf->SetFont('cinzelb', '', 42, '', false);
    $pdf->SetXY(0, 72);
    $pdf->Write(0, 'CERTIFICADO', '', 0, 'C', true, 0, false, false, 0);

    // Texto do nome
    $pdf->SetFont('Helvetica', '', 24, '');
    if(strlen($nome) > 55) {
        $pdf->SetXY(0, 101);
    } else {
        $pdf->SetXY(0, 106);
    }
    $pdf->Write(0, $nome, '', 0, 'C', true, 0, false, false, 0);

    // Texto do corpo
    $pdf->SetFont('montserratsemib', '', 14, '', false);
    $texto = '...';
    $pdf->SetXY(0, 94);
    $pdf->Write(0, $texto, '', 0, 'C', true, 0, false, false, 0);
    $texto = '...';
    $pdf->SetXY(0, 121);
    $pdf->Write(0, $texto, '', 0, 'C', true, 0, false, false, 0);
    // Data
    $pdf->SetXY(0, 131);
    $pdf->Write(0, '07 de julho de 2021.', '', 0, 'C', true, 0, false, false, 0);


    // Informações de salvamento
    // junta as partes do e-mail com _ para colocar no nome
    $suffix = implode($sep, explode('.', implode($sep, explode('@', $email))));
    $output_path = $save_dir . $file_name . $sep . $suffix . '.pdf';

    echo 'Gerando arquivo ' . $output_path . "\n";

    // Salvar
    $pdf->Output($output_path, 'I');
}

echo "\n";
