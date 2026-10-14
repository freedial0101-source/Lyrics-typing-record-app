<?php
require_once 'functions.php'; 
$user = "phpuser";
$password = ""; 
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
];

// //  [判定] もしボタンが押されて「POST」でデータが届いた場合
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    //     // ---  ここにボタンを押したときの処理を書く ---
    $json_text = $_POST['localData'] ?? '';
    $local_data = json_decode($json_text, true);
    try {
        $dbh = new PDO('mysql:host=localhost;dbname=typing_training', $user, $password, $opt);

        $sql = "INSERT INTO タイピングの記録 (id,曲名,スピード,正確性,日付,開始時間,終了時間,体調) 
                 VALUES (NULL , :title, :speed, :accuracy,:date, :start,:end,:health)";
        $stmt = $dbh->prepare($sql);
        // $price = (int) $_POST['price'];
        $stmt->bindParam(":title", $local_data["title"], PDO::PARAM_STR);
        $stmt->bindParam(":speed", $local_data["speed"], PDO::PARAM_INT);
        $stmt->bindParam(":accuracy", $local_data["accuracy"], PDO::PARAM_INT);
        $stmt->bindParam(":date", $local_data["date"], PDO::PARAM_STR);
        $stmt->bindParam(":start", $local_data["start"], PDO::PARAM_STR);
        $stmt->bindParam(":end", $local_data["end"], PDO::PARAM_STR);
        $stmt->bindParam(":health", $local_data["health"], PDO::PARAM_STR);
        $stmt->execute();
        // echo "訓練開始！  曲名:" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "開始時間:" . htmlspecialchars($time, ENT_QUOTES, 'UTF-8');
        echo "記録しました";
    } catch (PDOException $e) {
        echo "エラー!: " . str2html($e->getMessage()) . "<br>";
        exit;
    }
    exit;
} else {
}

?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>タイピングトレーニングの記録アプリ</title>

</head>

<body>


    <div>
        <h2 id="recentRecord"></h2>
    </div>

    <h3>トレーニング開始時に入力</h3>
    <label>曲名: <input type="text" name="title" id="title"></label>
    <label for="health">体調:</label>
    <select name="health" id="health">
        <option value="問題なし">問題なし</option>
        <option value="やや悪い">やや悪い（軽い寝不足など）</option>
        <option value="悪い">悪い（かなり寝不足、大きなショック）</option>
    </select>
    <button type="button" id="start">スタート！</button>


   
    <div id="result1" style="padding: 10px; background: #f0f0f0;">ここに結果が表示されます</div>


    <hr>
    <h3>トレーニング終了時に入力</h3>

    <label>速さ: <input type="text" id="speed">key/s</label>
    <label>正確性: <input type="text" id="accuracy">%</label>

    <form id="form2">
        <input type="hidden" id="localData" name="localData">
        <button type="submit" id="stop-button">ストップ！</button>
    </form>


    <div id="result2" style="padding: 10px; background: #f0f0f0;">ここに結果が表示されます</div>

    <hr>



    <hr>

    <script>
  
        //    ✏️グラフ表示機能を付ける
        const recentRecord = document.getElementById("recentRecord");
        let jsonText = localStorage.getItem('typingData');
        // typingDataが存在しない場合はnullとなる
        const typingData = JSON.parse(jsonText) || {};

        const updateRecentRecord = () => {

            if (jsonText !== null) {
                if (typingData.now == "done") {
                    recentRecord.innerText = `前回の記録:『${typingData.title}』${typingData.speed}key/s,${typingData.accuracy}%(${typingData.date}に挑戦)`;
                } else {
                    recentRecord.innerText = `『${typingData.title}』に挑戦中(${typingData.date}${typingData.start}開始)`;
                }
            } else {
                recentRecord.innerText = "（ローカルストレージに記録はありません）";
            }
        }
        updateRecentRecord();





        const result1 = document.getElementById('result1');
        const result2 = document.getElementById('result2');
        const title = document.getElementById('title');
        const health = document.getElementById('health');
        const speed = document.getElementById('speed');
        const accuracy = document.getElementById('accuracy');

        const localData = document.getElementById('localData');




    
        document.getElementById('start').addEventListener('click', () => {
       


            const now = new Date();
            const datePart = now.toLocaleDateString('sv-SE');
            const timePart = now.toLocaleTimeString('ja-JP', {
                hour12: false
            });
            const updateAtStart = () => {
                typingData.now = "doing";
                typingData.title = title.value;
                typingData.health = health.value;
                typingData.date = datePart;
                typingData.start = timePart;
                jsonText = JSON.stringify(typingData);
                localStorage.setItem('typingData', jsonText);

                recentRecord.innerText = `『${typingData.title}』に挑戦中(${typingData.start}開始)`;
                   result1.innerText=`トレーニングを開始しました!(『${typingData}』);`
            }

            if (typingData.now == "doing") {
                let result = confirm("未完了のデータを消します。よろしいですか？");

                if (result) {
                    alert("訓練を開始しました！");
                    updateAtStart();
                } else {
                    alert("キャンセルの場合は、終了データを入力してください！");
                }
            } else {
                updateAtStart();
            }

        });



        async function sendData(formId, result) {
            const form = document.getElementById(formId);
            const formData = new FormData(form);



            try {
                const response = await fetch("", {
                    method: 'POST',
                    body: formData
                });
                const resultText = await response.text();
                result.innerText = resultText;
            } catch (error) {
                result.innerText = '送信エラーが発生しました。';
            }
            
        }
        
        document.getElementById('form2').addEventListener('submit', function(event) {
            event.preventDefault();
            const now = new Date();
            const datePart = now.toLocaleDateString('sv-SE');
            const timePart = now.toLocaleTimeString('ja-JP', {
                hour12: false
            });
            if (typingData.now == "doing") {
                typingData.now = "done";
                typingData.speed = speed.value;
                typingData.accuracy = accuracy.value;
                typingData.end = timePart;
                jsonText = JSON.stringify(typingData);
                localStorage.setItem('typingData', jsonText);

                recentRecord.innerText = `『${typingData.title}』をやりました。(${typingData.speed}key/s,${typingData.accuracy}%)`;

                localData.value = jsonText;
                sendData('form2', result2);
            } else if (typingData.now == "done") {
                // if(typingData.speed==speed.value&&typingData.accuracy){
                alert("すでに終了処理をしています！");
                        } else {
                alert("開始データがありません！");
            }




        });
    </script>
</body>

</html>