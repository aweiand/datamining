<?php

/**
 * Esta Classe prove métodos úteis para utilização Geral
 *  
 * @author Augusto Weiand <guto.weiand@gmail.com>
 * @version 2.0
 * @access public
 * @name Utils
 * @category utilitarios  
 * @package utils
 */
class utils extends data {

        /**
         * Funcao para verificar se existem letras nao permitidas na sintaxe enviada
         * @access public 
         * @param $post array - variaves vindas por $_POST ou $_GET
         * @return bool - se ha valores nao permitidos ou nao
         */
        function badWords($post) {
                $badwords = array("#", "'", "*", "=", " union ", " insert ", " update ", " drop ", " select ");
                foreach ($post as $value)
                        foreach ($badwords as $word)
                                if (substr_count($value, $word) > 0)
                                        return false;
                                else
                                        return true;
        }

        /**
         * Funcao para testar se a pessoa esta logada no sistema
         * @access public 
         * @param $_SESSION['usuid'] String - Sessao que guarda o ID do usuario
         * @param $_SESSION['autenticado'] String - Sessao que define se o usuario esta autenticado ou nao 
         * @return bool - se ha valores nao permitidos ou nao
         */
        function _logado() {
                @session_start();
                if (isset($_SESSION['usuid']))
                        return true;
                else
                        return false;
        }

        /**
         * Função para verificar se o usuario tem as permissoes necessárias
         * @return boolean
         */
        function _autentica() {
                @session_start();
                $db = new data();
                if ($db->_get("alo.permissoes", "pessoa = " . $_SESSION['usuid'])->RecordCount() != 0)
                        return true;
                else
                        return false;
        }

        /**
         * Funcao para verificar as permissoes do usuario
         * @access public 
         * @param $codusuario String - ID do usuario
         * @deprecated - novo m�todo desenvolvido 
         * @return Array com as informa��es dos aplicativos permitidos
         */
        function getPermissoes($codusuario) {
                $ret['cadUser'] = 1;
                $ret['altUser'] = 1;
                $ret['delUser'] = 1;

                $ret['cadAluno'] = 1;
                $ret['altAluno'] = 1;
                $ret['delAluno'] = 1;

                $ret['aut'] = 1;

                return $ret;
        }

        /**
         * Funcao para envio de e-mails
         * @access public 
         * @param $assunto String - Assunto da Mensagem
         * @param $arq String - Localiza��o do template do E-mail
         * @param $dadosAdic Array - Dados do E-mail  
         * @return bool - com status de envio
         */
        function envEmail($assunto, $arq, $dadosAdic, $anexos = false) {
                $mt = new mail($arq);
                $from = '';

                if ((isset($dadosAdic['nomeFrom'])) && (isset($dadosAdic['emailFrom']))) {
                        $from = $dadosAdic['nomeFrom'] . "<" . $dadosAdic['emailFrom'] . ">";
                }
                else
                        $from = "CNEC EAD<cead@facos.edu.br>";

                if ((isset($dadosAdic['nome'])) && (isset($dadosAdic['email'])))
                        $to = $dadosAdic['nome'] . "<" . $dadosAdic['email'] . ">";
                else
                        $to = "CNEC EAD<cead@facos.edu.br>";

                $mt->setConfig("smtp.facos.edu.br", "cead@facos.edu.br", "cead2013", "587");

                if ($dadosAdic != '') {
                        foreach ($dadosAdic as $pos => $valor)
                                $mt->campos[$pos] = $valor;
                }

                $mt->assunto = $assunto;
                $mt->cabecalhos["From"] = $from;
                $mt->cabecalhos["To"] = $to;
                $mt->campos["momento_envio"] = @date("d-m-Y");

                $mt->parse();
                /*
                  echo $from;
                  echo $mt->send("phpmailer");
                  exit();
                 */
                if ($anexos)
                        foreach ($anexos as $a => $value) {
                                $mt->objMail->AddAttachment($_SERVER['DOCUMENT_ROOT'] . "/" . $value['link']);
                                //$mt->objMail->AddAttachment($_SERVER['DOCUMENT_ROOT'] . "/hubble/img/login-butn.png");
                        };

                $ret = $mt->send("phpmailer");
                if ($ret == 'ok')
                        return true;
                else
                        return false;  ////use "view" to debug ou "phpmailer" para envio










                        
//return $mt->send("mail");
        }

        /**
         * Funcao para gerar o proximo autoincremento de um campo
         * 	@access public
         * 	@param $tab String - tabela do banco de dados. Ex.: 'Usuario'
         *  @param $campo String - string com o nome da coluna. Ex.: 'codUsuario'
         *  @return Integer - valor do pr�ximo autoincremento
         * 
         */
        function seed($tab, $campo) {
                $sql = "SELECT NEXTVAL('" . $tab . "_" . $campo . "_seq')";
                $ret = parent::query($sql);

                return $ret->Fields('nextval');
        }

        /**
         * Funcao que monta o array para entregar para o flexigrid e ja o converte para array json
         * 	@access public
         * 	@param $bdtab String - tabela do banco de dados. Ex.: 'Usuario'
         *  @param $post $_POST
         *  @param $chave - os camposs que deverao ser selecionado
         *  @param $join - um ou mais join a ser feito para utilizacao de mais tabelas
         *  @return Integer - valor do pr�ximo autoincremento
         */
        function montaSqlGrid($bdtab, $post, $chave, $join = '') {
                $post = $_POST;

                $page = isset($post['page']) ? $post['page'] : 1;
                $rp = isset($post['rp']) ? $post['rp'] : 10;
                $sortname = isset($post['sortname']) ? $post['sortname'] : 'nome';
                $sortorder = isset($post['sortorder']) ? $post['sortorder'] : 'asc';
                $query = isset($post['query']) ? $post['query'] : false;
                $qtype = isset($post['qtype']) ? $post['qtype'] : false;

                $sort = "ORDER BY $sortname $sortorder";
                $start = (($page - 1) * $rp);
                $limit = "LIMIT $rp OFFSET $start";
                $where = "";

                if ($query)
                        $where = "WHERE UPPER($qtype) LIKE UPPER('%" . $query . "%')";

                $campos = implode($chave, ',');

                $sql = "SELECT $campos FROM $bdtab $join $where $sort $limit";

                $ret = parent::query($sql);

                $lin = parent::query("SELECT * FROM $bdtab ");

                $jsonData = array(
                    'page' => $page,
                    'total' => $lin->RecordCount(),
                    'rows' => array()
                );

                while (!$ret->EOF) {

                        $arr = array();
                        foreach (array_keys($chave) as $a) {
                                $arr[] = $ret->Fields($a);
                        }

                        $entry = array(
                            'id' => $arr[0],
                            'cell' => $arr,
                        );
                        $jsonData['rows'][] = $entry;
                        $ret->MoveNext();
                }

                echo json_encode($jsonData);
        }

        /**
         * Funcao que converte uma senha para um padr�o utilizado por LDAP
         * 	@access public
         * 	@param $senha String
         *  @deprecated - Muito velha e sem atualiza��o e utiliza��o
         *  @return String - Senha codificada
         */
        function str2pwd($senha) {
                $passwd = "{SHA}" . base64_encode(pack("H*", sha1($senha)));

                return $passwd;
        }

        /**
         * Funcao que retorna um array com todos arquivos do diretorio
         * 	@access public
         * 	@param $caminho String - Caminho do diretorio
         *  @param $mask String - Tipo de Arquivo
         *  @return Array com arquivos
         */
        function getFilesFromDir($caminho, $mask = "*") {
                $dir = @ dir("$caminho");

                //List files in images directory 
                while (($file = $dir->read()) !== false) {
                        if ($file != "." && $file != ".." && fnmatch($mask, $file))
                                $l_vdir[] = $file;
                }

                $dir->close();

                array_multisort($l_vdir);

                return($l_vdir);
        }

        /**
         * Funcao que converte as datas de acordo com o modo
         * 	@access public
         * 	@param $datahora date - Data a ser convertida
         *  @param $modo String - Modo de conversao
         *  @return String
         */
        function formatDateTime($datahora, $modo = "") {
                if ($datahora != "") {
                        // Separa data e hora.
                        $dh = explode(" ", $datahora);
                        $data = $dh[0];
                        if (isset($dh[1]) && $dh[1] != '00:00:00')
                                $hora = $dh[1];
                        else
                                $hora = '';
                        // Separa a data.
                        $d = explode("-", $data);
                        @$ano = $d[0];
                        @$mes = $d[1];
                        @$dia = $d[2];
                        @$data = $d[2] . "/" . $d[1] . "/" . $d[0];
                        if ($hora != "")
                                $h = explode(":", $hora);
                        else
                                $h = array();

                        if ($modo == "")
                                return $data . ' ' . $hora;
                        else
                        if ($modo == "dia_mes")
                                return $d[2] . "/" . $d[1];
                        else
                        if ($modo == "dia_mes_escrito")
                                return $d[2] . " de " . Utils::getNomeMes(intval($d[1]));
                        else
                        if ($modo == "data_traco_hora")
                                return $data . " - " . $hora;
                        else
                        if ($modo == "dia_mes_traco_hora_min")
                                return $d[2] . "/" . $d[1] . " - " . $h[0] . "h" . $h[1] . "min";
                        else
                        if ($modo == "data_hora")
                                return $data . ' ' . $h[0] . ":" . $h[1];
                        else
                        if ($modo == "data")
                                return $data;
                        else
                        if ($modo == "americano") {
                                //retorna a data no formato americano yyyy-mm-dd, recebendo
                                // por valor data dd/mm/yyyy
                                $d = explode('/', $datahora);
                                return $d[2] . "-" . $d[1] . "-" . $d[0];
                        } else
                        if ($modo == "americanoFull") {
                                //retorna a data no formato americano yyyy-mm-dd H:M:S, recebendo
                                // por valor data dd/mm/yyyy H:M:S
                                $d = explode('/', $datahora);
                                $ano = explode(" ", $d[2]);
                                return $ano[0] . "-" . $d[1] . "-" . $d[0] . " " . $ano[1];
                        } else
                        if ($modo == "brasComData") {
                                //retorna a data no formato brasileira dd-mm-yyyy H:M:S, recebendo
                                // por valor data yyyy/mm/dd H:M:S - 2012-07-31 14:00:00
                                $d = explode('-', $datahora);
                                $ano = explode(" ", $d[2]);
                                return $ano[0] . "-" . $d[1] . "-" . $d[0] . " " . $ano[1];
                        } else
                        if ($modo == "dia_escrito_mes_ano") {
                                setlocale(LC_ALL, 'pt_BR');
                                $tstamp = mktime(0, 0, 0, $mes, $dia, $ano);
                                $Tdate = getdate($tstamp);
                                return $Tdate['weekday'] . ' | ' . $dia . '-' . $mes . '-' . $ano;
                        }
                }
                else
                        return "";
        }

        /**
         * Funcao que retorna um array com o numero e meses por extenso 
         * 	@access public
         *  @return Array
         */
        function getMeses() {
                $mesext = array('1' => 'Janeiro', '2' => 'Fevereiro', '3' => 'Março', '4' => 'Abril',
                    '5' => 'Maio', '6' => 'Junho', '7' => 'Julho', '8' => 'Agosto',
                    '9' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro');
                return $mesext;
        }

        /**
         * Funcao que retorna o nome do mes 
         * 	@access public
         * 	@param $mes String - Numero do Mes
         *  @return String - Nome do Mes
         */
        function getNomeMes($mes) {
                $mesext = Utils::getMeses();
                return $mesext[$mes];
        }

        /**
         * Funcao que retorna o numero de dias de um mes 
         * 	@access public
         * 	@param $mes String - Numero do Mes
         *  @return Integer - Numero de dias do Mes
         */
        function getDiasMes($mes) {
                $ts = Utils::iso2unix(date("Y") . "-" . $mes . "-1");
                return date("t", $ts);
        }

        /**
         * Funcao que retorna o salt das senhas do Moodle 
         * 	@access public
         * 	@param $arquivo String com local do config do Moodle
         *  @return String - salt Utilizado
         */
        function getSaltFromMoodle($arquivo) {
                /*
                  $str = file_get_contents($arquivo);
                  $procurar = "/(?<=passwordsaltmain = ').*'/";

                  preg_match_all($procurar, $str, $arr);
                  print_r($arr);
                  $achou = $arr[0][0];
                  $achou = explode("'", $achou);
                  $achou = $achou[0];
                  if ($achou)
                  return $achou;
                 */
                return 'Nq2IIMl?nom.~GJ&]wS,T7LMEvRz';
        }

        /**
         * Fun��o para retirar acentos e caracteres especiais
         * 	@access public
         * 	@param String palavra
         * 	@return String
         */
        function tiraAcento($palavra) {
                $string = str_replace(" ", '_', $palavra);
                $string = iconv('UTF-8', 'ASCII//IGNORE', $palavra);
                return $string;
        }

        /**
         * Fun��o para verificar quantos meses tem entre as datas
         * 	@access public
         * 	@param String $startDate - Data Inicial yyyy-mm-dd
         * 	@param String $endDate   - Data Final yyyy-mm-dd
         * 	@return String
         */
        function monthsBetween($startDate, $endDate) {
                $retval = "";

                // Assume YYYY-mm-dd - as is common MYSQL format
                $splitStart = explode('-', $startDate);
                $splitEnd = explode('-', $endDate);

                if (is_array($splitStart) && is_array($splitEnd)) {
                        $difYears = $splitEnd[0] - $splitStart[0];
                        $difMonths = $splitEnd[1] - $splitStart[1];
                        $difDays = $splitEnd[2] - $splitStart[2];

                        $retval = ($difDays > 0) ? $difMonths : $difMonths - 1;
                        $retval += $difYears * 12;
                }
                return $retval;
        }

        /**
         *  Fun��o para recuperar Feeds do Twitter
         * 
         * 	REMEBER: When using HTML, escape double-quotations like this: \" 
         * 	$usernames = Pull from which accounts? Separated by a space, for example: Username Username Username
         * 	$limit = Number of tweets to pull in, total.
         * 	$show = Show username? 0 = No, 1 = Yes. 
         * 	$prefix_sub	= This comes before each tweet on the feed.
         * 	$wedge 		= This comes after the username but before the tweet content.
         * 	$suffix_sub = This comes after each tweet on the feed.
         *
         * 	Exemplo de uso: parse_feed();
         */
        function parse_feed($usernames = "CNEC_EAD", $limit = "5", $show = 1, $prefix_sub = "<li><p>", $wedge = "</p>", $suffix_sub = "</li>") {
                $str = "";
                $usernames = str_replace(" ", "+OR+from%3A", $usernames);
                $feed = "http://search.twitter.com/search.atom?q=from%3A" . $usernames . "&rpp=" . $limit;
                $feed = file_get_contents($feed);
                $feed = str_replace("&", "&", $feed);
                $feed = str_replace("<", "<", $feed);
                $feed = str_replace(">", ">", $feed);
                $clean = explode("<entry>", $feed);
                $amount = count($clean) - 1;

                for ($i = 1; $i <= $amount; $i++) {

                        $entry_close = explode("</entry>", $clean[$i]);
                        $clean_content_1 = explode("<content type=\"html\">", $entry_close[0]);
                        $clean_content = explode("</content>", $clean_content_1[1]);
                        $clean_name_2 = explode("<name>", $entry_close[0]);
                        $clean_name_1 = explode("(", $clean_name_2[1]);
                        $clean_name = explode(")</name>", $clean_name_1[1]);
                        $clean_uri_1 = explode("<uri>", $entry_close[0]);
                        $clean_uri = explode("</uri>", $clean_uri_1[1]);

                        $str.= $prefix_sub;

                        if ($show == 1) {
                                $str.= "<a href='" . $clean_uri[0] . "'>" . $clean_name[0] . "</a>" . $wedge;
                        }

                        $str.= $clean_content[0];
                        $str.= $suffix_sub;
                }

                $str = str_replace("&lt;", "<", $str);
                $str = str_replace("&gt;", ">", $str);

                return $str;
        }

        /**
         * Retorna um select ou multiselect
         * @param Integer $cod
         * @param String $table
         * @param Integer $key
         * @param String/Integer $data
         * @param string $name
         * @param RecordSet $rs
         * @param boolean $enUtf8
         * @return string
         */
        function getSelectDb($cod = false, $table, $key = 0, $data = "nome", $name = false, $rs = false, $enUtf8 = true, $class = "", $style = false) {
                if ($rs)
                        $rs = $rs;
                else
                        $rs = parent::_get($table);

                if (!$name)
                        $name = "sel" . $table;
                
                if (!$style)
                        $style = "width: 500px;";

                if (is_array($cod))
                        $str = "<select name='" . $name . "[]' id='" . $name . "' multiple='multiple' class='$class' style='$style' >";
                else
                        $str = "<select name='" . $name . "' id='" . $name . "' class='$class' style='$style'>";

                if (!$cod)
                        $str.= "<option selected='selected'>Selecione um Valor</option>";
                else
                        $str.= "<option>Selecione um Valor</option>";

                while (!$rs->EOF) {
                        if (is_array($data)) {
                                $value = "";
                                foreach ($data as $datas)
                                        $value.=$rs->Fields($datas) . " ";
                        }
                        else
                                $value = $rs->Fields($data);

                        if ($enUtf8)
                                $value = utf8_encode($value);

                        if (is_array($cod)) {
                                if ($this->array_search_recursive($rs->fields($key), $cod))
                                        $str.="<option value='" . $rs->Fields($key) . "' selected='selected'>" . $value . "</option>";
                                else
                                        $str.="<option value='" . $rs->Fields($key) . "'>" . $value . "</option>";
                        } else {
                                if (($cod) && $cod == $rs->fields($key))
                                        $str.="<option value='" . $rs->Fields($key) . "' selected='selected'>" . $value . "</option>";
                                else
                                        $str.="<option value='" . $rs->Fields($key) . "'>" . $value . "</option>";
                        }
                        $rs->MoveNext();
                }

                $str.= "</select>";

                if (is_array($cod)) {
                        $str.="	<script type='text/javascript'>
						$(function(){
							$('#" . $name . "').multiselect({
								mixWidth:550,
								maxWidth:800,
								selectedList:5,
								show: ['slide', 500],
								hide: ['explode', 500],
								autoOpen: false
							}).multiselectfilter();
						});
					</script>";
                }

                return $str;
        }

        /**
         * Função para retornar um select ou multiselect a partir dos dados passados
         * @param Array $param - com parâmetros:
         *      $cod - Integer / Array de códigos já selecionados
         *      $table - Nome da Tabela, se $rs não for passado, - REQUIRED
         *      $key - campo usado como value
         *      $data - String / Array - campos para mostrar - REQUIRED
         *      $name - String id e name do campo, se null id e name serão sel.$table
         *      $style - String com estilos
         *      $class - String com classes
         * @param type $rs - Recordset com dados
         * @return string
         */
        function getSelectImageDb($param, $rs = false) {
                $param = json_decode(json_encode($param), FALSE);

                if (!isset($param->key))
                        $param->key = 0;

                if ($rs)
                        $rs = $rs;
                else
                        $rs = parent::_get($param->table);

                if (!isset($param->name))
                        $param->name = "sel" . $param->table;

                if (!isset($param->style))
                        $param->style = "width: 350px;";

                if (!isset($param->class))
                        $param->class = "";

                if (!isset($param->image))
                        $param->image = "imagem";

                if (!isset($param->dir))
                        $param->dir = "";

                if (is_array($param->cod))
                        $str = "<select name='" . $param->name . "[]' id='" . $param->name . "' multiple='multiple' style='$param->style' class='$param->class'>";
                else
                        $str = "<select name='" . $param->name . "' id='" . $param->name . "' style='$param->style' class='$param->class'>";

                while (!$rs->EOF) {
                        if (is_array($param->data)) {
                                $value = "";
                                foreach ($param->data as $datas)
                                        $value.=$rs->Fields($datas) . " ";
                        }
                        else
                                $value = $rs->Fields($param->data);

                        if (isset($param->enUtf8))
                                $value = utf8_encode($value);

                        if (is_array($param->cod)) {
                                if ($this->array_search_recursive($rs->fields($param->key), $param->cod))
                                        $str.="<option value='" . $rs->Fields($param->key) . "' data-image='" . $param->dir . $rs->Fields($param->image) . "' selected='selected'>" . $value . "</option>";
                                else
                                        $str.="<option value='" . $rs->Fields($param->key) . "' data-image='" . $param->dir . $rs->Fields($param->image) . "'>" . $value . "</option>";
                        } else {
                                if (isset($param->cod) && $param->cod == $rs->fields($param->key))
                                        $str.="<option value='" . $rs->Fields($param->key) . "' data-image='" . $param->dir . $rs->Fields($param->image) . "' selected='selected'>" . $value . "</option>";
                                else
                                        $str.="<option value='" . $rs->Fields($param->key) . "' data-image='" . $param->dir . $rs->Fields($param->image) . "'>" . $value . "</option>";
                        }
                        $rs->MoveNext();
                }

                $str.= "</select>
                        
                <script type='text/javascript'>
                        $(function(){
                                $('#" . $param->name . "').msDropdown({";

                if (is_array($param->cod))
                        $str.= "multiple: true,
                                     enableCheckbox: true,";
                else
                        $str.= "multiple: false,
                                    enableCheckbox: false,";

                $str.="         visibleRows: 7,
                                      rowHeight: 5
                                });
                        });
                </script>";

                return $str;
        }

        /**
         * Função que pesquisa em um array multiplo pela agulha
         * @param Integer $needle
         * @param Array $haystack
         * @param Boolean $retornaKeyArrayMulti
         * @return Integer/Array
         */
        function array_search_recursive($needle, $haystack, $retornaKeyArrayMulti = false) {
                $path = array();
                foreach ($haystack as $id => $val) {
                        if ($val === $needle)
                                $path[] = $id;
                        else if (is_array($val)) {
                                $found = $this->array_search_recursive($needle, $val);
                                if (count($found) > 0) {
                                        $path[$id] = $found;
                                }
                        }
                }
                if ($retornaKeyArrayMulti)
                        return $path[$retornaKeyArrayMulti];
                else
                        return $path;
        }

        /**
         * Função que grava na tabela public.actlog os logs do sistema
         * @param String $action
         * @param String $modulo
         * @param Text $text
         * @param Integer $codusuario
         * @return boolean
         */
        function _insActlog($action = "_login", $modulo = "site", $text = "", $codusuario = false) {
                if ((!$codusuario) && isset($_SESSION['usuid']))
                        $arr['codusuario'] = $_SESSION['usuid'];
                else
                        $arr['codusuario'] = $codusuario;

                $arr['action'] = $action;
                $arr['modulo'] = $modulo;
                $arr['text'] = $text;
                $arr['ip'] = $_SERVER['REMOTE_ADDR'];

                if (parent::_insrt("public.actlog", $arr))
                        return true;
                else
                        return false;
        }

        /**
         * Função que retorna uma data formatada com a diferença de dias
         * entre dataini e datafim - retorna -> dia+mes+ano
         * @param Date $datafim
         * @param Date $dataini
         * @return String
         */
        function dias($datafim, $dataini = false) {
                if (!$dataini)
                        $dataini = @date("Y-m-d");

                //echo "<b>Data: ".$dataini." - ".$datafim;	

                $datafim = explode("-", $datafim);
                $dataini = explode("-", $dataini);

                $ano = ($datafim[0] - $dataini[0]) * 365;
                $mes = ($datafim[1] - $dataini[1]) * 30;
                $dia = $datafim[2] - $dataini[2];

                if ($mes > 1)
                        $dia++;

                //echo " | ano: ".$ano." - mes: ".$mes." - dia: ".$dia." = ".($dia+$mes+$ano)."</b><br>";

                return $dia + $mes + $ano;
        }

        /**
         * Função que retorna um string no formato X,X,X para ser usado em SQL na 
         * clausula IN
         * @param RecordSet $rs
         * @param String/Integer $field
         * @return String
         */
        function getArrayIn($rs, $field) {
                $in = array();
                while (!$rs->EOF) {
                        $in[] = $rs->Fields($field);
                        $rs->MoveNext();
                };

                $in = implode(",", $in);
                return $in;
        }

        /**
         * Função que retorna um array com o registro passado no RecordSet
         * @param Recordset $rs
         * @param String/Integer $field
         * @return Array
         */
        function getArraySelectDB($rs, $field) {
                $in = array();
                while (!$rs->EOF) {
                        $in[] = $rs->Fields($field);
                        $rs->MoveNext();
                };
                return $in;
        }

        /**
         * Calcula a diferença de várias maneira, conforme os dados de entrada
         * 
         * 
          "m" Minútos
          "H" Horas
          "h": Horas arredondada
          "D": Dias
          "d": Dias arredontados
         * 
         * 
         * @param date $data1 - Data final
         * @param date $data2 - Data Inicial
         * @param String $tipo - String com o tipo de retorno esperado
         * @return Real - Retorno conforme o tipo solicitado
         */
        function dateDiff($data1, $data2 = false, $tipo = "m") {
                if (!$data1)
                        $data1 = @date("Y-m-d h:m:s");

                for ($i = 1; $i <= 2; $i++) {
                        ${"dia" . $i} = substr(${"data" . $i}, 8, 2);
                        ${"mes" . $i} = substr(${"data" . $i}, 5, 2);
                        ${"ano" . $i} = substr(${"data" . $i}, 0, 4);
                        ${"horas" . $i} = substr(${"data" . $i}, 11, 2);
                        ${"minutos" . $i} = substr(${"data" . $i}, 14, 2);
                        ${"segundos" . $i} = substr(${"data" . $i}, 17, 2);
                }

                $segundos = mktime($horas2, $minutos2, $segundos2, $mes2, $dia2, $ano2) - mktime($horas1, $minutos1, $segundos1, $mes1, $dia1, $ano1);

                switch ($tipo) {
                        case "s": $difere = $segundos;
                                break;
                        case "m": $difere = $segundos / 60;
                                break;
                        case "H": $difere = $segundos / 3600;
                                break;
                        case "h": $difere = round($segundos / 3600);
                                break;
                        case "D": $difere = $segundos / 86400;
                                break;
                        case "d": $difere = round($segundos / 86400);
                                break;
                }

                return $difere;
        }

        /**
         * Função para criar um grupo de radio com imagens ou sem, 
         * porém com o uso do bootstrap, pra ficar bonitinhu :D
         * @param Array $param
         *      $cod - Integer / Array de códigos já selecionados
         *      $table - Nome da Tabela, se $rs não for passado, - REQUIRED
         *      $key - campo usado como value
         *      $data - String / Array - campos para mostrar - REQUIRED
         *      $name - String id e name do campo, se null id e name serão sel.$table
         *      $dir - String com base do diretorio
         *      $image - String name do campo
         * @param type $rs - Recordset com dados
         * @return string
         * @param RecordSet $rs
         * @return Mixed
         */
        function getRadioImage($param, $rs = false) {
                $param = json_decode(json_encode($param), FALSE);

                if (!isset($param->key))
                        $param->key = 0;

                if ($rs)
                        $rs = $rs;
                else
                        $rs = parent::_get($param->table);

                if (!isset($param->name))
                        $param->name = "sel" . $param->table;

                if (!isset($param->dir))
                        $param->dir = "/";

                if (!isset($param->width))
                        $param->width = 30;

                $str = "    <div id='btn-rdio-$param->name' class='btn-group' data-toggle='buttons-radio'>";
                $click = "";

                while (!$rs->EOF) {
                        if (isset($param->cod) && $param->cod == $rs->Fields($param->key))
                                $str.= "<button type='button' class='btn btn-primary active' name='$param->name' value='" . $rs->Fields($param->key) . "'>";
                        else
                                $str.= "<button type='button' class='btn btn-primary' name='$param->name' value='" . $rs->Fields($param->key) . "'>";

                        if (isset($param->image))
                                $str.="<img src='$param->dir" . $rs->Fields($param->image) . "' width='$param->width' />";

                        $str.= $rs->Fields($param->data) . "</button>";

                        if (isset($param->cod) && $param->cod == $rs->Fields($param->key))
                                $str.= " <input type='radio' style='display: none;' name='$param->name' id='rdio-" . $rs->Fields($param->key) . "' value='" . $rs->Fields($param->key) . "' checked='checked' />";
                        else
                                $str.= " <input type='radio' style='display: none;' name='$param->name' id='rdio-" . $rs->Fields($param->key) . "' value='" . $rs->Fields($param->key) . "' />";

                        $rs->MoveNext();
                };

                $str.= "</div>
                        <script>
                                $(function(){
                                        $('#btn-rdio-$param->name button').each(function(){
                                                $(this).on('click', function(){
                                                        $('#rdio-'+$(this).val()).click();
                                                        subareaLoad($(this).val());
                                                })
                                        });
                                })
                        </script>";
                return $str;
        }

}
