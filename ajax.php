<?php
    include "connect.php";
    $data_id = 0;
    if( isset($_POST['name']) && is_string($_POST['name']) && trim($_POST['name']) &&
        isset($_POST['data_id']) && is_numeric($_POST['data_id']) && (int)$_POST['data_id'] > 0
    ) {
        $name = trim($_POST['name']);
        $data_id = (int)$_POST['data_id'];

        // ADD CHILD
        $sql = $pdo->prepare("SELECT COUNT(0) count FROM tree WHERE parenthood = ?");
        $sql->bindValue(1, $data_id);
        $sql->execute();
        $count = $sql->fetchColumn();
        $return['count'] = $count;

        $query = $pdo->prepare("INSERT INTO tree(name, parenthood) VALUES('$name', '$data_id')");
        $query->execute();

        $id = $pdo->query("SELECT MAX(id) id FROM tree");
        $id = $id->fetchColumn();
        $return['id'] = $id;

        $return['status'] = 'successfully added!';
    }
    // DELETE ALL
    else if( isset($_POST['dataid']) && is_numeric($_POST['dataid']) && (int)$_POST['dataid'] > 0 ) {
        $data_id = (int)$_POST['dataid'];
        $query = $pdo->prepare("DELETE FROM tree WHERE id = ? OR parenthood = ?");
        $query->bindValue(1, $data_id);
        $query->bindValue(2, $data_id);
        $query->execute();
        $return['status'] = 'successfully deleted!';
    }

    // SINGLE DELETE
    else if( isset($_POST['data']) && is_numeric($_POST['data']) && (int)$_POST['data'] > 0 ) {
        $data_id = (int)$_POST['data'];

        $count = $pdo->prepare("SELECT COUNT(0) FROM tree WHERE parenthood = $data_id");
        $count->execute();
        $count = $count->fetchColumn();
        $return['count'] = $count;

        $parent = $pdo->prepare("SELECT parenthood FROM tree WHERE id = $data_id");
        $parent->execute();
        $parent = $parent->fetchColumn();
        $return['parent'] = $parent;

        $query = $pdo->prepare("DELETE FROM tree WHERE id = ?");
        $query->bindValue(1, $data_id);
        $query->execute();

        $sql = $pdo->prepare("UPDATE tree SET parenthood = ? WHERE parenthood = ?");
        $sql->bindValue(1, $parent);
        $sql->bindValue(2, $data_id);
        $sql->execute();

        $return['status'] = 'deleted!';
    }

    // ADD PARENT
    else if(
        isset($_POST['addData_id']) && is_numeric($_POST['addData_id']) && (int)$_POST['addData_id'] > 0 &&
        isset($_POST['prnt_name']) && is_string($_POST['prnt_name']) && trim($_POST['prnt_name'])
    ){
        $data_id = (int)$_POST['addData_id'];
        $name = trim($_POST['prnt_name']);

        $ask = $pdo->prepare("SELECT COUNT(0) FROM tree WHERE parenthood = $data_id");
        $ask->execute();
        $count = $ask->fetchColumn();

        $query = $pdo->prepare("INSERT INTO tree(name, parenthood) VALUES('$name', '$data_id')");
        $query->execute();

        $id = $pdo->query("SELECT MAX(id) id FROM tree");
        $id = $id->fetchColumn();

        $child = $pdo->prepare("SELECT id FROM tree WHERE parenthood = $data_id AND id != $id");
        $child->execute();
        $child = $child->fetchColumn();

        $sql = $pdo->prepare("UPDATE tree SET parenthood=? WHERE parenthood=? AND id != ?");
        $sql->bindValue(1, $id);
        $sql->bindValue(2, $data_id);
        $sql->bindValue(3, $id);
        $sql->execute();

        $name = $pdo->prepare("SELECT name FROM tree WHERE id = $id");
        $name->execute();
        $name = $name->fetchColumn();


        $return['count'] = $count;
        $return['child'] = $child;
        $return['name'] = $name;
        $return['id'] = $id;
        $return['status'] = "parent added!";
    }

    else $return['status'] = "error!";
    echo json_encode($return);
