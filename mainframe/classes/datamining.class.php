<?php

/**
 * @author Augusto Weiand <guto.weiand@gmail.com>
 * @version 0.1
 * @access public
 * @name datamining
 * @category tablesManipulate  
 * @package datamining
 */
class datamining extends utils {

        var $prefix;
        var $db;

        /**
         * Esta função construtora, define a classe que sera utilizada para os selects
         * @param String $db - nome da classe de banco de dados a ser instanciada
         * @param String $prefix - prefixo das tabelas do moodle
         */
        function datamining($db = "data", $prefix = "moodle_") {
                $this->prefix = $prefix;
                $this->db = new $db;
        }

        /**
         * Calcula o intervalo entre duas datas no formato ISO, o intervalo é dado
         * no formato específicado em intevalor q pode ser
         * y - ano
         * m - meses
         * d - dias
         * h - horas
         * n - minutos
         * default ´se gundos
         *
         * @param string $data1
         * @param string $data2
         * @param string $intervalo m, d, h, n,y
         * @return int|string intervalo de horas
         */
        public static function dataDif($data1, $data2, $intervalo) {

                switch ($intervalo) {
                        case 'y':
                                $Q = 86400 * 365;
                                break; //ano
                        case 'm':
                                $Q = 2592000;
                                break; //mes
                        case 'd':
                                $Q = 86400;
                                break; //dia
                        case 'h':
                                $Q = 3600;
                                break; //hora
                        case 'n':
                                $Q = 60;
                                break; //minuto
                        default:
                                $Q = 1;
                                break; //segundo
                }

                return round((strtotime($data2) - strtotime($data1)) / $Q);
        }

        /**
         * Função que retorna os professores do curso selecionado
         * @param type $curso
         * @return type
         */
        function getProfessroesCurso($curso) {
                $rs = $this->db->query("SELECT rs.userid as id
                FROM " . $this->prefix . "course c
                INNER JOIN " . $this->prefix . "context e ON (c.id = e.instanceid )
                INNER JOIN " . $this->prefix . "role_assignments rs ON (e.id = rs.contextid)
                WHERE rs.roleid = 3
                AND c.id = $curso");

                $prof = array();
                while (!$rs->EOF) {
                        $prof[] = $rs->Fields("id");
                        $rs->MoveNext();
                };
                return $prof;
        }

        /**
         * Função que retorna os alunos participantes do curso
         * @param type $curso
         * @return type
         */
        function getAlunosCurso($curso) {
                $rs = $this->db->query("SELECT rs.userid as id
                FROM " . $this->prefix . "course c
                INNER JOIN " . $this->prefix . "context e ON (c.id = e.instanceid )
                INNER JOIN " . $this->prefix . "role_assignments rs ON (e.id = rs.contextid)
                WHERE e.contextlevel = 50 AND c.id = $curso");

                $aluno = array();
                while (!$rs->EOF) {
                        $aluno[$rs->Fields("id")] = $rs->Fields("id");
                        $rs->MoveNext();
                };
                return $aluno;
        }

        function getOrganizacao($curso, $idforum) {
                $alunos = $this->getAlunosCurso($curso);
                $org = array();

                $forum = $this->db->query("SELECT * FROM " . $this->prefix . "forum
                                                                        WHERE id = $idforum");

                if ($forum->Fields("assesstimestart") == 0 && $forum->Fields("assestimefinish") == 0)
                        return "-1";

                $discus = $this->db->query("SELECT * FROM " . $this->prefix . "forum_discussions
                                                                        WHERE forum = $idforum");

                while (!$discus->EOF) {
                        $post = $this->db->query("SELECT MAX(p.modified) as modif, p.userid
                                                                                FROM " . $this->prefix . "forum_posts p
                                                                        WHERE p.discussion = " . $discus->Fields("id") . "
                                                                                GROUP BY p.userid");

                        while (!$post->EOF) {
                                if ($post->Fields("modif") < $forum->Fields("assesstimefinish") && $post->Fields("modif") > $forum->Fields("assesstimestart")) {
                                        if ($this->dataDif(date("d-m-Y", $post->Fields("modif")), date("d-m-Y", $forum->Fields("assesstimefinish"), "d")) < 4)
                                                $org[$post->Fields("userid")]["org"]++;
                                        else
                                                $org[$post->Fields("userid")]["org"]--;
                                }
                                $post->MoveNext();
                        }

                        $discus->MoveNext();
                }
                /*
                 * PAssar um por um vendo se é positivo tem, se nao nao tem
                 */

                return $org;
        }

        /**
         * Função que retorna o número do módulo do fórum 
         * @param type $course
         * @param type $forum
         * @return type
         */
        function getModId($course, $forum) {
                $qry = "SELECT cm.id as modid
                                FROM " . $this->prefix . "course_modules cm
                                        JOIN " . $this->prefix . "modules md ON (md.id = cm.module)
                                        JOIN " . $this->prefix . "forum m ON (m.id = cm.instance)
                                WHERE 
                                        cm.course = $course AND m.id = $forum";
                return $this->db->query($qry)->Fields("modid");
        }

        /**
         * Função que retorna se o aluno possui ou não Fluência Digital
         * @param type $course
         * @param type $forum
         * @return boolean
         */
        function getFluenciaDigital($course, $forum) {
                $alunos = $this->getAlunosCurso($course);

                foreach ($alunos as $key => $data) {
                        $rs = $this->db->query("SELECT COUNT(id) as count
                                                        FROM " . $this->prefix . "log 
                                                                WHERE 
                                                        course = $course AND 
                                                        cmid = " . $this->getModId($course, $forum) . "
                                                        AND userid = $key");
                        if ($rs->Fields("count") >= 7)
                                $alunos[$data] = true;
                        else
                                $alunos[$data] = false;
                }
                return $alunos;
        }

        function getAutonomia($course, $forum) {
                
        }

        /**
         * Função que relaciona o numero de posts para encontrar a competencia comunicacao
         * @param type $course
         * @param type $forum
         * @return boolean
         */
        function getComunicacao($course, $forum) {
                $alunos = $this->getAlunosCurso($course);
                
                foreach ($alunos as $key => $data){
                        $alunos[$key] = 0;
                }

                $discus = $this->db->query("SELECT id FROM " . $this->prefix . "forum_discussions
                                                                        WHERE forum = $forum");

                while (!$discus->EOF) {
                        $rs = $this->db->query("SELECT COUNT(*) as count, p.userid
                                                                                FROM " . $this->prefix . "forum_posts p
                                                                        WHERE p.discussion = " . $discus->Fields("id") . "
                                                                                GROUP BY p.userid");
                        while (!$rs->EOF) {
                                $alunos[$rs->Fields("userid")] += $rs->Fields("count");
                                $rs->MoveNext();
                        }
                        $discus->MoveNext();
                }

                foreach ($alunos as $key => $data) {
                        if ($data > 7)
                                $alunos[$key] = true;
                        else
                                $alunos[$key] = false;
                }

                return $alunos;
        }

        /**
         * Função que relaciona qualquer ação no forum a presença virtual
         * @param type $course
         * @param type $forum
         * @return boolean
         */
        function getPresencialidadeVirtual($course, $forum) {
                $alunos = $this->getAlunosCurso($course);

                foreach ($alunos as $key => $data) {
                        $rs = $this->db->query("SELECT COUNT(id) as count
                                                                        FROM " . $this->prefix . "log
                                                                WHERE course = $course
                                                                AND cmid = " . $this->getModId($course, $forum) . "
                                                                AND userid = $data");
                        if ($rs->Fields("count") > 7)
                                $alunos[$data] = true;
                        else
                                $alunos[$data] = false;
                }
                return $alunos;
        }

}