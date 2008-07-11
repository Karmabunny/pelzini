<?php
require_once 'functions.php';


$_GET['id'] = (int) $_GET['id'];

if ($_GET['id'] == 0) {
  unset ($_SESSION['current_package']);
  header('Location: index.php');
  
} else {
  $_SESSION['current_package'] = $_GET['id'];
  header('Location: package.php?id=' . $_GET['id']);
}
?>
