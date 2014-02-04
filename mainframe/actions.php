<?php
require_once "autoload.php";

@session_cache_expire(90); // 2 hours
@session_start();

/* 
 */
$post = $_POST;
$get = $_GET;

/*
  echo "<pre>";
  print_r($post);
  exit();
 */

//##################################
$dbm = new datamining();
//#################################

if (isset($get['action'])) {
        switch ($get['action']) {

        }
};

if (isset($post['action'])) {
        switch ($post['action']) {
        	case "getForumCursos" : {
        		echo $dbm->getSelectForum($post['curso']);
        	} break;
        }
};