<?php

/**
 * Esta Classe prove uma API de Manipula��o do Banco de Dados porem ela necessita de que seja instanciada juntamente com ela
 *  o pacote ADOBD for PHP que pode ser baixado em:	http://adodb.sourceforge.net/
 *  
 * @author Augusto Weiand <guto.weiand@gmail.com>
 * @version 0.9  
 * @access public
 * @name data
 * @category DatabaseManipulate  
 * @package data
 */
class data {

        var $db;
        var $debug = false;

        /**
         * Função de construção da classe, definindo as variáveis
         */
        function data() {
                @define('ADODB_FORCE_IGNORE', 0);
                @define('ADODB_FORCE_NULL', 1);
                @define('ADODB_FORCE_EMPTY', 2);
                @define('ADODB_FORCE_VALUE', 3);

                $this->db = $this->startDB();
        }

        /**
         * Funcao que define e prepara a conexao com o BD
         * @access public 
         * @return mixed - Conexao
         */
        function startDB() {
            $server = "localhost";
            //$server   = "postgresql02.facos.edu.br";            
            $database = "facos10";
            $user = "facos11";
            $password = "Alf@j0r260";

                $CONN = ADONewConnection('postgres');
                $CONN->Connect($server, $user, $password, $database);
                $CONN->Execute("SET NAMES utf-8;");
                $CONN->debug = $this->debug;
                return $CONN;
        }

        /**
         * Funcao para execucao de comandos DML
         * @access public 
         * @param String $cmdSQL - Query a ser executada, somente DML (Select, Insert, Update, Delete)
         * @return Recordset - com dados da Consulta Realizada
         */
        function query($cmdSQL) {
                $this->db->debug = $this->debug;
                $rsQ = $this->db->Execute($cmdSQL) or $this->showErro($this->db->ErrorMsg(), $cmdSQL);
                return $rsQ;
        }

        /**
         * Funcao para execucao de comandos DDL
         * @access public
         * @param String $cmdSQL - Query a ser executada, somente DDL (Create, Alter, Drop)
         * @return Recordset - com dados do resultado obtido
         */
        function command($cmdSQL) {
                $ret = $this->query($cmdSQL) or $this->gravaLog('_command', $this->db->ErrorMsg() . " ####SQL#### " . $cmdSQL);
                return $ret;
        }

        /**
         * Funcao para capturar erros de execu��o
         * @access public
         * @param String $msg - mensagem capturada
         * @param String $cmdSQL - Query executada 
         * @return String - echo com erro encontrado
         */
        function showErro($msg, $cmdSQL = "") {
                if ($cmdSQL != "")
                        trigger_error($msg . " comando SQL: <strong><pre>" . $cmdSQL . "</pre></strong>", E_USER_WARNING);
                return false;
        }

        /**
         * Funcao que retorna uma SQL de Inser��o de Dados
         * @access public
         * @param String $rs - Tabela do Banco de Dados. Ex.: 'Usuario'
         * @param String $data - Array com Dados a serem processados. Ex.: $arr['nome'] = 'Augusto' ; $arr['e-mail'] = 'guto.weiand@gmail.com'
         * @return String - query de insert com os dados passados nos parametros
         */
        function getInsertSQL($rs, $data) {
                $a = $this->db->GetInsertSQL($rs, $data);
                return $a;
        }

        /**
         * Funcao para inserir registros sem sql
         * @access public
         * @param String $rs - Tabela do Banco de dados a ser utilizada. Ex.: 'Usuario'
         * @param Array $record - Array com dados a serem inseridos. Ex.: $arr['nome'] = 'Augusto' ; $arr['e-mail'] = 'guto.weiand@gmail.com'
         * @return Recordset - com dados da Inser��o Realizada.
         */
        function _insrt($rs, $record) {
                //$this->db->debug=true;
                $ret = $this->db->AutoExecute($rs, $record, "INSERT") or $this->gravaLog('_insrt', $this->db->ErrorMsg());
                return $ret;
        }

        /**
         * Funcao para atualizar registros de uma tabela
         * @access public
         * @param String $rs - tabela do banco de dados. Ex.: 'Usuario'
         * @param String $record - Array com dados a serem inseridos. Ex.: $arr['nome'] = 'Augusto' ; $arr['e-mail'] = 'guto.weiand@gmail.com'
         * @param String $cod - codigo de referencia para atualizacao. Ex.: id = 1
         * @return Recordset - com dados da Consulta Realizada
         */
        function _updt($rs, $record, $cod) {
                //$this->db->debug=true;
                $ret = $this->db->AutoExecute($rs, $record, 'UPDATE', $cod) or $this->gravaLog('_updt', $this->db->ErrorMsg() . " ###COD### " . $cod . " ###TABLE### " . $rs);
                return $ret;
        }

        /**
         * Funcao para gerar o proximo autoincremento de um campo
         * 	@access public
         * 	@param String $tab - tabela do banco de dados. Ex.: 'Usuario'
         *  @param String $campo - string com o nome da coluna. Ex.: 'codUsuario'
         *  @return Integer - valor do pr�ximo autoincremento
         * 
         */
        function seed($tab, $campo) {
                $sql = "SELECT NEXTVAL('" . $tab . "_" . $campo . "_seq')";
                $ret = $this->query($sql);
                return $ret->Fields('nextval');
        }

        /**
         * Funcao para gravar logs na tabela
         * @access public
         * @param String $action - acao executada pelo usuario
         * @param String $dado - dados a serem gravados
         * @return bool
         */
        function gravaLog($action, $dado, $table = 'public') {
                $dado = str_replace('\'', ' ', $dado);
                $cmdSQL = "INSERT INTO " . $table . ".log VALUES(" . $this->seed('log', 'cod_log') . ",'" . $action . "','" . $dado . "',NOW())";
                if ($this->query($cmdSQL))
                        return true;
                else
                        return false;
        }

        /**
         * M�todo generico que retorna as informa��es da tabela
         * passada, juntamente com os joins ou where necessarios
         *
         * 	@access public	 
         * 	@param String $tabela - tabela ( Ex: usuario u )	 
         * 	@param String $join - par�metro de join ( Ex: plano on (outra.id = outro.id) )	 
         * 	@param String $where - par�metro de where ( Ex: codusuario = 1 )	 
         * 	@param String $order - par�metro de ordenacao ( Ex: codusuario ASC )	 
         * 	@return recordset
         */
        function _get($tabela, $where = false, $join = false, $order = false) {
                $cmdSQL = "SELECT * FROM " . $tabela;
                if ($join)
                        $cmdSQL.=" INNER JOIN " . $join;
                if ($where)
                        $cmdSQL.=" WHERE " . $where;
                if ($order)
                        $cmdSQL.=" ORDER BY " . $order;
                else
                        $cmdSQL.=" ORDER BY 1 ASC, 2 ASC";

                //echo $cmdSQL;

                $ret = $this->query($cmdSQL);
                //$db->gravaLog('_query',$cmdSQL);
                return $ret;
        }

        /**
         * Função que seta o debug
         * @param Boolean $debug
         */
        function setDebug($debug) {
                $this->debug = $debug;
        }

}

?>
