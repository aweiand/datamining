<?php
require_once "mainframe/autoload.php";
@session_start();

if (!isset($_GET['curso']) || !isset($_GET['forum'])){
        echo "<h2>Falta algum destes parâmetros no link...</h2>";
        echo "<pre>
                \$_GET[\"course\"]
                \$_GET[\"forum\"]
                </pre>";
        echo "<p><i>Exemplo:</i> index.php?curso=1&forum=2</p>";
        exit;
}

$curso = $_GET['curso'];
$forum = $_GET['forum'];

$dbm = new datamining();
//echo '<pre>';
//print_r($dbm->getProfessroesCurso($curso));                                                
$alunos = $dbm->getAlunosCurso($curso);
//print_r($dbm->getModId($curso, $forum));
$org = $dbm->getOrganizacao($curso, $forum);
$flu = $dbm->getFluenciaDigital($curso, $forum);
$auto = $dbm->getAutonomia($curso, $forum);
$com = $dbm->getComunicacao($curso, $forum);
$virt = $dbm->getPresencialidadeVirtual($curso, $forum);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta name="application-name" content="CEAD" /> 
                <meta name="author" content="FACOS - Augusto Weiand augusto@facos.edu.br" />
                <meta name="keywords" content="Data Mining" />
                <meta property="og:title" content="Data Mining"/>
                <meta property="og:type" content="website"/>
                <meta property="og:site_name" content="Data Mining"/>
                <meta property="og:description" content="Data Mining"/>
                <meta name="robots" content="index, follow" />
                <meta name="title" content="Data Mining" />
                <meta name="description" content="Data Mining" />

                <title>Data Mining - Moodle</title>

                <!-- CSS Struct -->	
                <link rel="stylesheet" type="text/css" href="mainframe/plugins/jquery/css/custom-theme/jquery-ui-1.10.2.custom.min.css" />
                <link rel="stylesheet" type="text/css" href="mainframe/plugins/bootstrap/css/bootstrap.min.css" />
                <link rel="stylesheet" type="text/css" href="mainframe/plugins/jquery/DataTables-1.9.4/media/css/jquery.dataTables_themeroller.css" />
                <link rel="stylesheet" type="text/css" href="css/core.css" />

                <!-- JS Struct -->
                <script type="text/javascript" src="mainframe/plugins/jquery/js/jquery-1.9.1.js"></script>
                <script type="text/javascript" src="mainframe/plugins/jquery/js/jquery-ui-1.10.2.custom.min.js"></script>
                <script type="text/javascript" src="mainframe/plugins/bootstrap/js/bootstrap.min.js"></script>
                <script type="text/javascript" src="mainframe/plugins/jquery/DataTables-1.9.4/media/js/jquery.dataTables.min.js"></script>
                <script type="text/javascript" src="js/utils.js"></script>
        </head>
        <body>
                <div class="center row-fluid">
                        <h3>Faculdade Cenecista de Osório - FACOS</h3>
                        <h4>Curso de Licenciatura em Computação</h4>
                </div>

                <div class="row-fluid" style="background: url('/alo/img/fundo.png') repeat-x scroll center top #E8ECD8">
                        <div class="center">
                                <div class="content span" style="display:block;">
                                        <h5>Curso - <?= ($dbm->getDadoCurso($curso)->Fields("fullname")) ?></h5>
                                        <h5>Fórum - <?= ($dbm->getDadoForum($forum)->Fields("name")) ?></h5>
                                        <h6>Total de Alunos - <?= count($alunos) ?> / Dia(s) de Fórum - <?= $dbm->getDiasForum($forum) ?></h6>
                                        <table class="table table-striped" id='tblData'>
                                                <thead>
                                                        <tr>
                                                                <th width="170">
                                                                        Aluno
                                                                </th>
                                                                <th>
                                                                        Organização
                                                                </th>
                                                                <th>
                                                                        Fluência Digital
                                                                </th>
                                                                <th>
                                                                        Autonomia
                                                                </th>
                                                                <th>
                                                                        Comunicação
                                                                </th>
                                                                <th>
                                                                        Presencialidade Virtual
                                                                </th>
                                                        </tr> 
                                                </thead>
                                                <tbody>
                                                        <?php
                                                        $str = "";
                                                        $pos = array("org" => 0, "flu" => 0, "auto" => 0, "com" => 0, "virt" => 0);
                                                        $nPos = array("org" => 0, "flu" => 0, "auto" => 0, "com" => 0, "virt" => 0);
                                                        foreach ($alunos as $key => $data) {
                                                                $str.= "
                                                                                <tr>
                                                                                        <td>
                                                                        $key
                                                                        </td>";
                                                                if ($org[$key] == false) {
                                                                        $str.= "<td style='background-color: #f2dede;'>Tende a Não Possuir</td>";
                                                                        $pos['org']++;
                                                                } else {
                                                                        $str.= "<td style='background-color: #dff0d8;'>Tende a Possuir</td>";
                                                                        $nPos['org']++;
                                                                }

                                                                if ($flu[$key] == false) {
                                                                        $str.= "<td style='background-color: #f2dede;'>Tende a Não Possuir</td>";
                                                                        $pos['flu']++;
                                                                } else {
                                                                        $str.= "<td style='background-color: #dff0d8;'>Tende a Possuir</td>";
                                                                        $nPos['flu']++;
                                                                }

                                                                if ($auto[$key] == false) {
                                                                        $str.= "<td style='background-color: #f2dede;'>Tende a Não Possuir</td>";
                                                                        $pos['auto']++;
                                                                } else {
                                                                        $str.= "<td style='background-color: #dff0d8;'>Tende a Possuir</td>";
                                                                        $nPos['auto']++;
                                                                }

                                                                if ($com[$key] == false) {
                                                                        $str.= "<td style='background-color: #f2dede;'>Tende a Não Possuir</td>";
                                                                        $pos['com']++;
                                                                } else {
                                                                        $str.= "<td style='background-color: #dff0d8;'>Tende a Possuir</td>";
                                                                        $nPos['com']++;
                                                                }

                                                                if ($virt[$key] == false) {
                                                                        $str.= "<td style='background-color: #f2dede;'>Tende a Não Possuir</td>";
                                                                        $pos['virt']++;
                                                                } else {
                                                                        $str.= "<td style='background-color: #dff0d8;'>Tende a Possuir</td>";
                                                                        $nPos['virt']++;
                                                                }

                                                                $str.="</tr>";
                                                        }
                                                        echo $str;
                                                        ?>
                                                </tbody>
                                                <tfoot>
                                                        <tr>
                                                                <td>
                                                                        Possuir / Não Possuir
                                                                </td>
                                                                <td>
                                                                        <?= $pos['org']." / ".$nPos['org'] ?>
                                                                </td>
                                                                <td>
                                                                        <?= $pos['flu']." / ".$nPos['flu'] ?>
                                                                </td>
                                                                <td>
                                                                        <?= $pos['auto']." / ".$nPos['auto'] ?>
                                                                </td>
                                                                <td>
                                                                        <?= $pos['com']." / ".$nPos['com'] ?>
                                                                </td>
                                                                <td>
                                                                        <?= $pos['virt']." / ".$nPos['virt'] ?>
                                                                </td>
                                                        </tr>
                                                </tfoot>
                                        </table>
                                </div>
                        </div>
                </div>

                <div class="center row-fluid">
                        <div class='span6'>
                                <p>
                                        <h5>Aluno</h5>
                                        Augusto Weiand - <a href='mailto:guto.weiand@gmail.com'>guto.weiand@gmail.com</a>
                                        <h5>Orientador</h5>
                                        Andrio dos Santos Pinto - <a href='mailto:andriosp@gmail.com'>andriosp@gmail.com</a>
                                </p>
                        </div>
                </div>

                <script>
                        shorTable("#tblData");
                </script>

        </body>
</html>
