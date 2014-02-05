<?php
require_once "mainframe/autoload.php";
@session_start();
$dbm = new datamining();
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
                <?php echo "<script type='text/javascript'>
                                var _CFG = '$CFG->affix';
                        </script>"; 
                ?>
                <script type="text/javascript" src="https://www.google.com/jsapi"></script>
                <script type="text/javascript" src="mainframe/plugins/jquery/js/jquery-1.9.1.js"></script>
                <script type="text/javascript" src="mainframe/plugins/jquery/js/jquery-ui-1.10.2.custom.min.js"></script>
                <script type="text/javascript" src="mainframe/plugins/bootstrap/js/bootstrap.min.js"></script>
                <script type="text/javascript" src="mainframe/plugins/jquery/DataTables-1.9.4/media/js/jquery.dataTables.min.js"></script>
                <script type="text/javascript" src="js/utils.js"></script>

                <script type="text/javascript">
                    // Load the Visualization API and the piechart package.
                    google.load('visualization', '1', {packages: ['charteditor', 'corechart']});

                    var localGoogle = google;
                </script>
        </head>
        <body>
                <div class="center row-fluid">
                        <h3>Faculdade Cenecista de Osório - FACOS</h3>
                        <h4>Curso de Licenciatura em Computação</h4>
                </div>
                <?php
                        if (!isset($_GET['curso']) || !isset($_GET['forum'])){
                                echo "  <h2>Falta algum destes parâmetros no link...</h2>

                                        <pre>
                                        \$_GET[\"course\"]
                                        \$_GET[\"forum\"]
                                        </pre>

                                        <p><i>Exemplo:</i> index.php?curso=1&forum=2</p>
                                        <fieldset class='center row-fluid' style='text-align: right;'>
                                                <legend>Dados</legend>
                                                <form action='./' method='GET'>
                                                        ". $dbm->getSelectCurso(@$_GET['curso']) ."
                                                        ". $dbm->getSelectForum(@$_GET['curso'], @$_GET['forum']) ."
                                                        <button class='btn btn-info'>
                                                                <i class='icon icon-ok'></i> Consultar
                                                        </button>
                                                </form>
                                                <hr />
                                        </fieldset>";   
                                exit();
                        }

                        $curso = $_GET['curso'];
                        $forum = $_GET['forum'];

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

                <div class="row-fluid" style="background: #E8ECD8">
                        <div class="center">
                                <fieldset class='center row-fluid' style='text-align: right;'>
                                        <legend>Dados</legend>
                                        <form action='./' method='GET'>
                                                <?php
                                                        echo $dbm->getSelectCurso(@$_GET['curso']); 
                                                        echo $dbm->getSelectForum(@$_GET['curso'], @$_GET['forum']); 
                                                        echo "  <button class='btn btn-info'>
                                                                        <i class='icon icon-ok'></i> Consultar
                                                                </button>";

                                                        if (isset($_GET['curso']) && isset($_GET['forum'])){
                                                                echo "  <p style='line-height: 3;'>
                                                                                <button type='button' class='btn btn-small btn-primary' onclick=\"$('#tblResults').toggle('slow'); return false;\">
                                                                                        <i class='icon icon-resize-full'></i> Expandir Resultados
                                                                                </button>

                                                                                <button type='button' class='btn btn-small' onclick='openEditor()'>
                                                                                        <i class='icon icon-edit'></i> Editor de Gráfico
                                                                                </button>
                                                                        </p>";
                                                        }
                                                ?>
                                        </form>
                                        <hr />
                                </fieldset>
                                <div class="content span" style='display: block;'>
                                        <h5>Curso - <?= ($dbm->getDadoCurso($curso)->Fields("fullname")) ?></h5>
                                        <h5>Fórum - <?= ($dbm->getDadoForum($forum)->Fields("name")) ?></h5>
                                        <h6>Total de Alunos - <?= count($alunos) ?> / Dia(s) de Fórum - <?= $dbm->getDiasForum($forum) ?></h6>
                                </div>

                                <div class="content span" style='display: block;'>
                                        <h4>Gráficos</h4>
                                        <div id="_relGraph"></div>
                                </div>
                                
                                <div class="content span" id='tblResults' style='display: none;'>
                                        <h4>Dados</h4>
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

                <script type='text/javascript'>
                        var wrapper;

                        function drawVisualization() {
                          wrapper = new google.visualization.ChartWrapper({
                          chartType: 'ColumnChart',
                          dataTable: [ ['Competência', 'Tendem a Ter', 'Tendem a Não Ter'],
                                       ['Organização', <?= $pos['org'] ?>, <?= $nPos['org'] ?>],
                                       ['Fluência Digital', <?= $pos['flu'] ?>, <?= $nPos['flu'] ?>],
                                       ['Autonomia', <?= $pos['auto'] ?>, <?= $nPos['auto'] ?>],
                                       ['Comunicação', <?= $pos['com'] ?>, <?= $nPos['com'] ?>],
                                       ['Presencialidade Virtual', <?= $pos['virt'] ?>, <?= $nPos['virt'] ?>]
                                     ],
                            options: {
                              'title': 'Tendência dos alunos no Fórum/Curso Analizado',
                              backgroundColor: { fill:'transparent' },
                              hAxis: {title: "Competências"}
                            },
                          containerId: '_relGraph'
                        });
                        wrapper.draw();
                        }

                        function openEditor() {
                          var editor = new localGoogle.visualization.ChartEditor();
                          localGoogle.visualization.events.addListener(editor, 'ok',
                            function() {
                              wrapper = editor.getChartWrapper();
                              wrapper.draw(document.getElementById('_relGraph'));
                          });
                          editor.openDialog(wrapper);
                        }                    
                
                        $(function(){
                                shorTable("#tblData");

                                drawVisualization();
                        })
                </script>

        </body>
</html>
