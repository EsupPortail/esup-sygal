<?php
/**
 * @var $this \Zend\View\Renderer\PhpRenderer
 * @var $controller \UnicaenCode\Controller\Controller
 * @var $viewName string
 */

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Filter\NomFichierTheseFormatter;
use Application\Service\Fichier\FichierService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\File\FileService;
use UnicaenApp\Util;

$REAL_APP_PATH = '/var/www/sygal';

$CSV_OUTPUT_FILE_PATH = '/tmp/' . uniqid('fichiers-divers-etat-') . '.csv';

/** @var FichierService $fichierService */
$fichierService = $controller->getServiceLocator()->get(FichierService::class);
/** @var FichierTheseService $fichierTheseService */
$fichierTheseService = $controller->getServiceLocator()->get('FichierTheseService');
/** @var FileService $fileService */
$fileService = $controller->getServiceLocator()->get(FileService::class);

$repo = $fichierTheseService->getEntityManager()->getRepository(Fichier::class);
$qb = $repo->createQueryBuilder('f');
$qb
    ->addSelect('nf, t, d, i')
    ->join('f.nature', 'nf')
    ->join('f.these', 't')
    ->join('t.doctorant', 'd')
    ->join('d.individu', 'i')
    ->where('1 = pasHistorise(f)')
    ->andWhere($qb->expr()->notIn('nf.code', [NatureFichier::CODE_THESE_PDF, NatureFichier::CODE_FICHIER_NON_PDF]))
    ->addOrderBy('nf.code')
    ->addOrderBy('f.nom')
    ->addOrderBy('f.histoModification', 'desc');
/** @var Fichier[] $fichiers */
$fichiers = $qb/*->setMaxResults(50)*/->getQuery()->getResult();

$nomFichierFormatter = new NomFichierTheseFormatter();

$updatedFichiers = [];
foreach ($fichiers as $fichier) {
    $newNom = $nomFichierFormatter->filter($fichier);
    $updateRequired = $newNom !== $fichier->getNom();

    if ($updateRequired) {
        $fichier->setNom($newNom);
        $updatedFichiers[] = $fichier;
    }
}
?>


<?php
$shellTemplate = 'mv "%s" "%s"';
$shells = [];
$shellUndos = [];

$sqlTemplate = "update fichier set NOM = '%s' where ID = '%s' ;";
$sqls = [];
$sqlUndos = [];

$precName = null;
$i = 1;

$data = [];
$header = [
    'n'        => "#",
    'nature'   => "Nature",
    'nom'      => "Nom à corriger",
    'date'     => "Date dépôt",
    'perdu'    => "Perdu",
    'these'    => "Thèse",
    'doct'     => "Doctorant",
    'nomc'     => "Nom corrigé",
    'shell'    => "Shell",
    'shellu'   => "Shell undo",
    'sql'      => "SQL",
    'sqlu'     => "SQL undo",
];
foreach ($updatedFichiers as $fichier) {

    $no = $fichier->getNomOriginal();
    $nc = $fichier->getNom();

    $isLast = $precName !== $fichier->getNomOriginal();

    if ($isLast) {
        // shell
        $srcPath = $fichierService->computeDestinationDirectoryPathForFichier($fichier) . '/' . $no;
        $destPath = $fichierService->computeDestinationDirectoryPathForFichier($fichier) . '/' . $nc;
        $shell = sprintf($shellTemplate, $srcPath, $destPath);
        $shells[] = $shell;
        // shell undo
        $shellUndo = sprintf($shellTemplate, $destPath, $srcPath);
        $shellUndos[] = $shellUndo;

        // SQL
        $sql = sprintf($sqlTemplate, $nc, $fichier->getId());
        $sqls[] = $sql;
        // SQL undo
        $sqlUndo = sprintf($sqlTemplate, $no, $fichier->getId());
        $sqlUndos[] = $sqlUndo;
    } else {
        $shell = '';
        $shellUndo = '';
        $sql = '';
        $sqlUndo = '';
    }

    $row = [
        'n'        => $i++,
        'nature'   => $fichier->getNature()->getCode(),
        'nom'      => $no,
        'date'     => $fichier->getHistoModification()->format('d/m/Y H:i:s'),
        'perdu'    => $isLast ? 'N' : 'O',
        'these'    => $fichier->getThese()->getId(),
        'doct'     => $fichier->getThese()->getDoctorant(),
        'nomc'     => $nc,
        'shell'    => $shell,
        'shellu'   => $shellUndo,
        'sql'      => $sql,
        'sqlu'     => $sqlUndo,
        'last'     => $isLast,
    ];
    $data[] = $row;

    $precName = $no;
}
?>

<?php
$s = Util::arrayToCsv($data, $header);
file_put_contents($CSV_OUTPUT_FILE_PATH, $s);
?>
<div>
    <strong>Obtention du fichier CSV créé :</strong> <pre>cp <?php echo $CSV_OUTPUT_FILE_PATH ?> ./code/</pre>
</div>

<hr>

<table class="table table-hover">
    <thead>
    <tr>
        <?php foreach ($header as $col): ?>
        <th><?php echo $col ?></th>
        <?php endforeach ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $row): ?>
        <?php
        $isLast = $row['last'];
        $color = $isLast ? 'inherit' : 'red';

        $no = $row['nom'];
        $nc = $row['nomc'];
        ?>
        <tr style="color:<?php echo $color ?>">
            <td><?php echo $row['n'] ?></td>
            <td><?php echo $row['nature'] ?></td>
            <td><?php echo $no ?></td>
            <td><?php echo $row['date'] ?></td>
            <td><?php echo $row['perdu'] ?></td>
            <td><a href="https://sygal.normandie-univ.fr/these/identite/<?php echo $row['these'] ?>"><?php echo $row['these'] ?></a></td>
            <td><?php echo $row['doct'] ?></td>
            <td><?php echo $nc ?></td>

            <?php if ($isLast): ?>
            <td><?php echo $row['shell']; ?></td>
            <td><?php echo $row['shellu']; ?></td>
            <td><?php echo $row['sql']; ?></td>
            <td><?php echo $row['sqlu']; ?></td>
            <?php endif ?>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<pre><?php echo implode(PHP_EOL, str_replace('/app', $REAL_APP_PATH, $shells)) ?></pre>
<pre><?php echo implode(PHP_EOL, $sqls) ?></pre>
