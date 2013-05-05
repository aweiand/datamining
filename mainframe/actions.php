<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/tcc/mainframe/autoload.php";

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
$db = new data();
$uti = new utils();
//#################################

if (isset($get['action'])) {
        switch ($get['action']) {

        }
};

if (isset($post['action'])) {
        switch ($post['action']) {

        }
};