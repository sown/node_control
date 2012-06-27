<?php

//$dbh = new PDO("mysql:dbname=node_config;host=localhost", "auth-https", "6itL6cbuwz=fd");
global $dbh;
$dbh = new PDO("mysql:dbname=sown_data;host=localhost", "root", "") or die("WTF");

function query($q, $params, $type) {
        global $dbh;
        $sth = $dbh->prepare($q);
        $sth->execute($params);
        return $sth->fetchObject($type);
}

function queryID($q, $params) {
        global $dbh;
        $sth = $dbh->prepare($q);
        $sth->execute($params);
        return $sth->fetchColumn(0);
}

function insert($q, $params) {
        global $dbh;
        $sth = $dbh->prepare($q);
        $sth->execute($params);
        return $dbh->lastInsertId();
}
