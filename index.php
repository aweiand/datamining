<?php
require_once "mainframe/autoload.php";
@session_start();
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
                <link rel="stylesheet" type="text/css" href="<?= $CFG->www ?>mainframe/plugins/jquery/css/custom-theme/jquery-ui-1.10.2.custom.min.css" />
                <link rel="stylesheet" type="text/css" href="<?= $CFG->www ?>mainframe/plugins/bootstrap/css/bootstrap.min.css" />
                <link rel="stylesheet" type="text/css" href="<?= $CFG->www ?>css/core.css" />

                <!-- JS Struct -->
                <script type="text/javascript" src="<?= $CFG->www ?>mainframe/plugins/jquery/js/jquery-1.9.1.js"></script>
                <script type="text/javascript" src="<?= $CFG->www ?>mainframe/plugins/jquery/js/jquery-ui-1.10.2.custom.min.js"></script>
                <script type="text/javascript" src="<?= $CFG->www ?>mainframe/plugins/bootstrap/js/bootstrap.min.js"></script>
                <script type="text/javascript" src="<?= $CFG->www ?>js/utils.js"></script>
        </head>
        <body>
                <div class="header row-fluid">
                        Topo
                </div>

                <div class="row-fluid" style="background: url('/alo/img/fundo.png') repeat-x scroll center top #E8ECD8">
                        <div class="center">
                                <div class="content span" style="display:block;">
                                        <?php
                                                $dbm = new datamining();
                                                echo '<pre>';
                                                //print_r($dbm->getProfessroesCurso(128));
                                                
                                                //print_r($dbm->getAlunosCurso(128));
                                                
                                                print_r($dbm->getModId(128, 470));
                                                echo "<br />";
                                                
                                                //ESTE ESTA COM PROBLEMAS!!::  print_r($dbm->getOrganizacao(128, 470));
                                                
                                                //print_r($dbm->getFluenciaDigital(128, 470));
                                                
                                                //ESTE ESTA EM DEV!!:: print_r($dbm->getAutonomia(128, 470));
                                                
                                                //print_r($dbm->getComunicacao(128, 470));
                                                
                                                //print_r($dbm->getPresencialidadeVirtual(128, 470));
                                        ?>
                                </div>
                        </div>
                </div>

                <div class="footer row-fluid">
                        Faculdade Cenecista de Osório - FACOS
                        <br />
                        Augusto Weiand - <a href='mailto:guto.weiand@gmail.com'>guto.weiand@gmail.com</a>
                        <br />
                        Orientador Andrio dos Santos Pinto - <a href='mailto:andriosp@gmail.com'>andriosp@gmail.com</a>
                </div>

        </body>
</html>
