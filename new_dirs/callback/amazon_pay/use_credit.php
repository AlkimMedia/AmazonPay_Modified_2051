<?php

chdir('../../');
require_once 'includes/application_top.php';

$_SESSION['cot_gv'] = !empty($_POST['use_credit']);

require_once 'includes/application_bottom.php';