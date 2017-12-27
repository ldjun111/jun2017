<?php
set_time_limit(0);
mysql_connect("localhost","shengwu","shengwu123");
mysql_select_db("shengwu");
mysql_query("set names utf8");
$dbctype = array();
function db_list($sql) {
	if(defined('Dbg')){ echo $sql."\n\n";}
	error_log($sql."\n",3,$_SERVER['DOCUMENT_ROOT'].'/log/sql'.date("Ymd").'.txt');
	$ret=mysql_query($sql);
	$rows = array();
	while($rs = @mysql_fetch_assoc($ret)){
		$rows[] = $rs;
	}
	return $rows;
}
function db_one($sql) {
	$A = db_list($sql);
	if (count ( $A ) > 0)return $A[0];
	else return NULL;
}
function db_qry($sql) {
	if(defined('Dbg')){ echo $sql."\n\n";}
	error_log($sql."\n",3,$_SERVER['DOCUMENT_ROOT'].'/log/sql'.date("Ymd").'.txt');
	$ret = mysql_query($sql);
	return mysql_affected_rows();
}
function db_ins() {
	return mysql_insert_id();
}
function db_coltype($p_table=''){
	global $dbctype;
	if($dbctype[$p_table]) return $dbctype[$p_table];
	$cols = db_list("desc ".$p_table);
	foreach($cols as $rs){
		$field = $rs['Field'];
		$type = $rs['Type'];
		if(strtolower(substr($type,0,3))=='int' || strtolower(substr($type,0,7))=='tinyint'
			 || strtolower(substr($type,0,8))=='smallint' || strtolower(substr($type,0,6))=='bigint'){
			$dbctype[$p_table][$field] = 'i';
		}else if(strtolower(substr($type,0,4))=='char'){
			$dbctype[$p_table][$field] = 's';//未知类型都用ta
		}else if(strtolower(substr($type,0,7))=='varchar' || strtolower(substr($type,0,4))=='text' || strtolower(substr($type,0,8))=='longtext'){
			$dbctype[$p_table][$field] = 'ta';//未知类型都用ta
		}else if(strtolower(substr($type,0,8))=='datetime'){	//date放上面
			$dbctype[$p_table][$field] = 'dt';
		}else if(strtolower(substr($type,0,4))=='date'){
			$dbctype[$p_table][$field] = 'd';
		}else if(strtolower(substr($type,0,5))=='float'){
			$dbctype[$p_table][$field] = 'f';
		}else{
			$dbctype[$p_table][$field] = 'od';
		}
	}
	return $dbctype[$p_table];
}
function db_add($p_table='',$p_data=array()) {
	global $dbctype;
	db_coltype($p_table);
	$sql_ins = "insert into $p_table(";
	$sql_value = " values(";
	foreach($p_data as $key=>$val){
		$name = $key;
		$value = $val;
		$type = $dbctype[$p_table][$name];
		if(!$type) continue;
		$sql_ins .= $name.',';
		if($type=="s" || $type=="d" || $type=="ta" || $type=="dt" || $type == "od"){
			$sql_value .= '"'.addslashes($value).'",';
		}else if($type=='i'){
			$sql_value .= intval($value).',';
		}else if($type=='f'){
			$sql_value .= floatval($value).',';
		}
	}
	$sql_ins = substr($sql_ins,0,strlen($sql_ins)-1).')';
	$sql_value = substr($sql_value,0,strlen($sql_value)-1).')';
	$sql = $sql_ins.$sql_value;
	return db_qry($sql);
}
function db_upd($p_table='',$p_data=array(),$p_wc=''){
	global $dbctype;
	if(!$p_wc) return false;
	db_coltype($p_table);
	$sql_upd = "update $p_table set ";
	foreach($p_data as $key=>$val){
		$name = $key;
		$value = $val;
		$type = $dbctype[$p_table][$name];
		if(!$type) continue;
		if($type=="s" || $type=="d" || $type=="ta" || $type=="dt"){
			$sql_upd .= $name.'="'.addslashes($value).'",';
		}else if($type=='i'){
			$sql_upd .= $name.'='.intval($value).',';
		}else if($type=='f'){
			$sql_upd .= $name.'='.floatval($value).',';
		}
	}
	$sql_upd = substr($sql_upd,0,strlen($sql_upd)-1);
	$sql = $sql_upd." ".$p_wc;
	return db_qry($sql);
}
?>
