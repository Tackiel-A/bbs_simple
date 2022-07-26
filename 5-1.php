<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="5-1.css">
    <title>簡易掲示板</title>
</head>
<body bgcolor="green" text="white">
    <?php

        //基本設定
        error_reporting(E_ALL & ~E_NOTICE);//noticeを出なくする
        //送信ボタンの名前
        if(empty($edit)){
            $submit_name = "送信";//新規投稿モード
        } else {
            $submit_name = "編集確定";//編集モード
        }
        $admin_pass = "Mun1";//管理者用パスワード   ※全削除に必要です。空欄にしないでください。

        //データベースへの接続
        $dsn = 'database here';//データベース定義
        $user = 'username here';//ユーザー名
        $pass = 'password here';//パスワード
        $pdo = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

        //データベースとカラムを作成
        $sql = "CREATE TABLE IF NOT EXISTS tbtest"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name TEXT,"
        . "comment TEXT,"
        . "date TEXT,"
        . "password TEXT"
        .");";
        //AUTO INCRIMENTの初期化
        $sql = 'SET @i = 0';
        $sql = 'UPDATE tbtest SET id = (@i := @i +1)';
        $sql = 'ALTER TABLE tbtest AUTO_INCREMENT = 1';
        $stmt = $pdo->query($sql);
        
        //関数の定義
        function func_alert($message){
            echo "<script>alert('$message');</script>";
        }

        //データの受け取り
        if($_POST["name"]){
            $_name = $_POST["name"];
        } else {
            $_name = "名無し";
        }
        $_commemt = $_POST["comment"];//コメントを取得
        $_commemt = str_replace(PHP_EOL, "<br>", $_commemt);//改行を処理
        $password = $_POST["password"];

        /*書き込み＆編集の処理*/

        if(isset($_POST["submit"])){
            if(!$_POST["hidden"]){
                //新規投稿の処理
                if($_POST["comment"]!=null){
                    $sql = $pdo -> prepare("INSERT INTO tbtest (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
                    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                    $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                    $name = $_name;//「名前」を受信
                    $comment = $_commemt;//「コメント」を受信
                    $date = date("Y年m月d日 H:i:s");//「日付」を受信
                    $password = $_POST["password"];
                    $sql -> execute();
                    echo "書き込み成功！";
                } else { //もしコメント欄が空欄の場合は
                    func_alert("コメントを入力してください");
                }
            } else {
                //編集の処理
                $sql = 'SELECT * FROM tbtest';//テーブルの選択
                $stmt = $pdo->query($sql);
                $results = $stmt->fetchAll();
                //変更するIDを取得
                $id = $_POST["hidden"];
                //編集内容を取得
                $e_name = $_POST["name"];
                $e_comment = $_POST["comment"];
                $e_password = $_POST["password"];
                //書き換え処理
                $sql = 'UPDATE tbtest SET name=:name,comment=:comment,password=:password WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $e_name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $e_comment, PDO::PARAM_STR);
                $stmt->bindParam(':password', $e_password, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                echo "投稿番号".$_POST["hidden"]."番：編集しました";
            }
        }
        
        /*削除機能の実装*/
        if($_POST["delete"]!=null){
            if (isset($_POST['put_delete'])){
                $delete = $_POST['delete'];
                $delete = htmlspecialchars ($delete);
            }
            //MYSQLのデータを取得
            $sql = 'SELECT * FROM tbtest';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach($results as $row){
                if($row["id"]==$delete){
                    if($row["password"]&&$row["password"]==$password){
                        $id = $_POST["delete"];
                        $sql = 'DELETE from tbtest where id=:id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        //確認メッセージ
                        echo "投稿番号".$_POST["delete"]."番：削除しました";
                        $sql = 'ALTER TABLE from tbtest AUTO_INCREMENT = 1';
                        //番号の振り直し処理
                        $sql = "use tbtest";
                        $sql = "set @n = 0;";
                        $sql = "";
                        break;
                    } elseif($row["password"]&&$row["password"]!=$password){
                        func_alert("投稿番号".$_POST["delete"]."番：パスワードが違います");
                        $edit = 0;
                        break;
                    } else {
                        func_alert("投稿番号".$_POST["delete"]."番：パスワードが未設定です");
                        break;
                    }
                }
            }
        }

        /*編集番号の指定*/
        if($_POST["edit"]!=null){
            if (isset($_POST['put_edit'])){
                $edit = $_POST['edit'];
                $edit = htmlspecialchars ($edit);
            }
            //MySQLのデータベースからデータを取得
            $sql = 'SELECT * FROM tbtest';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                if($row["id"]==$edit){
                    if($row["password"]&&$row["password"]==$password){//パスワードが一致した場合
                        $i_name = $row['name'];
                        $i_comment = $row['comment'];
                        $i_password = $row["password"];
                        echo "投稿番号".$edit."番：編集しています";
                        break;
                    } elseif($row["password"]&&$row["password"]!=$password){//パスワードが不一致の場合
                        func_alert("投稿番号".$edit."番：パスワードが違います");
                        $edit = 0;
                        break;
                    } else {
                        func_alert("投稿番号".$edit."番：パスワードが未設定です");
                        break;
                    }
                }
            }
        }

        //全削除ボタン
        if(isset($_POST["all_delete"])){
            if($admin_pass==$_POST["all_del_pass"]){
                //データベースを削除
                $sql = 'DROP TABLE tbtest';
                $stmt = $pdo->query($sql);
                //データベースを再び作成
                $sql = "CREATE TABLE IF NOT EXISTS tbtest"
                ." ("
                . "id INT AUTO_INCREMENT PRIMARY KEY,"
                . "name TEXT,"
                . "comment TEXT,"
                . "date TEXT,"
                . "password TEXT"
                .");";
                $stmt = $pdo->query($sql);
                //確認メッセージ
                echo "すべてのデータを削除しました";
            } else {
                func_alert("管理者用パスワードが違います");
            }
        }
    ?>

    <form action="" method="post" bgcolor="black" text="white" >
        <div id='form'>
        <label for="name">お名前:</label>
        <input type="text" name="name" value="<?php if($i_name){ echo $i_name;}?>" placeholder="名前を入力"><br>
        <textarea name="comment" id="comment" cols="50" rows="10" placeholder = "コメントを入力"><?php if($i_comment){ echo $i_comment;}?></textarea><br>
        <input type="submit" name="submit" value="<?php echo $submit_name; ?>">
        <input type="text" name="password" value="<?php if($i_password){ echo $i_password;}?>" placeholder="パスワード入力"><br>
        <input type="number" name="delete" placeholder="削除対象番号">
        <input type="submit" name="put_delete" value="削除">
        <input type="number" name="edit" placeholder="編集対象番号">
        <input type="submit" name="put_edit" value="編集">
        <input type="hidden" name="hidden" value="<?php if($edit){ echo $edit;}?>">
        </div>
    </form>

    <?php
        $sql = 'SELECT * FROM tbtest';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo "<div id='box'>";
            echo $row['id'].'.';
            echo "<strong>".$row['name']."</strong>";
            echo "(".$row['date'].")<br>";
            echo $row['comment'];
            echo "</div>";
        }
        echo "<hr>";
    ?>

    <form action="" method="post">
        <input type="submit" name="all_delete" value="全削除">
        <input type="text" name="all_del_pass" placeholder="管理者用パスワード">
    </form>

</body>
</html>