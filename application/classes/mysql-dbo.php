<?

//$dbh = new PDO("mysql:dbname=node_config;host=localhost", "auth-https", "6itL6cbuwz=fd");
global $dbh;
$dbh = new PDO("mysql:dbname=sown_admin;host=localhost", "root", "") or die("WTF");

function query($q, $params, $type) {
        global $dbh;
        $sth = $dbh->prepare($q);
        $sth->execute($params);
        return $sth->fetchObject($type);
}

function insert($q, $params) {
        global $dbh;
        $sth = $dbh->prepare($q);
        $sth->execute($params);
        return $dbh->lastInsertId();
}

?>
