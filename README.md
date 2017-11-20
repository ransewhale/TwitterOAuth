# TwitterOAuth
TwitterのAPIを簡単に扱えるようにする

## 使い方

0. include関数かrequire関数，もしくはinclude_once関数かrequire_once関数を用いて _TwitterOAuth.php_ を読み込んでください。

1. <<https://apps.twitter.com/>>でアプリを作成し，Consumer KeyとConsumer Secretを取得し，_config\_template.php_ をコピーして _config.php_ に改名し， _config.php_ の該当箇所にコピー&ペーストする。

2. アプリを作成したアカウントでAPIを動かす場合， apps.twitter.comで _OAuth Token_ と _OAuth Token Secret_ も取得し，手順 _6_ に飛ぶ。

3. 次のようにして _Access Token_ と _Access Token Secret_ を取得する(ソースコード中の _CALLBACK\_URL_ は認証後に飛ばされるURLを指しています)。

        $API = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET);
        $token = $API->request_token(CALLBACK_URL);
        // Access Token は $token["oauth_token"] に入っています(以下ソースコード中ではACCESS_TOKENとします)。
        // Access Token Secret は $token["oauth_token_secret"] に入っています(以下ソースコード中ではACCESS_TOKEN_SECRETとします)。

    この時必ず oauth_callback_confirmed が _true_ であることを確認する。

        if($token["oauth_callback_confirmed"] !== "true"){
            // エラー処理
        }

4. _Access Token_ を利用してTwitter側の認証ページ(URLは次に記す)にユーザーを飛ばす(ここで何らかの形で _Access Token Secret_ は保存しておく)。

        // https://api.twitter.com/oauth/authenticate?oauth_token=ACCESS_TOKEN

5. _CALLBACK\_URL_ に飛ばされた時に _oauth\_verifier_ を与えられる(ソースコード中では _OAUTH\_VERIFIER_ とします)ので，それを利用して次のように _OAuth Token_ と _OAuth Token Secret_ を取得する。

        $API = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,ACCESS_TOKEN,ACCESS_TOKEN_SECRET);
        $token = $API->access_token(OAUTH_VERIFIER);
        // OAuth Token は $token["oauth_token"] に入っています(以下ソースコード中ではOAUTH_TOKENとします)。
        // OAuth Token Secret は $token["oauth_token_secret"] に入っています(以下ソースコード中ではOAUTH_TOKEN_SECRETとします)。

6. 取得した _OAuth Token_ と _OAuth Token Secret_ を利用して「おっぱい」とつぶやく。

        $user = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,OAUTH_TOKEN,OAUTH_TOKEN_SECRET);
        $status = $user->tweet("おっぱい");

7. 「おっぱい」とつぶやいたらそのツイートに「にゃーん」というリプをぶら下げてみる。

       $user->tweet("おっぱい",$status->id);

## 仕様

TwitterOAuthクラスのメゾッドの一覧です。これらのメゾッドは基本的にAPIから返ってきたJSON(やその他の形のデータ)を配列形式になおして返します。

### POST($url,$params)

POST送信を要求するAPIに対して使えます。

* $url ... APIのURL
* $params ... API

### GET($url,$params)

GET送信を要求するAPIに対して使えます。

* $url ... APIのURL
* $params ... API

### request_token($callback)

 _Access Token_ と _Access Token Secret_ をリクエストするときに使えます。       

* $callback ... CALLBACK URL

### access_token($oauth_verifier)

 _OAuth Token_ と _OAuth Token Secret_ をリクエストするときに使えます。       

* $oauth_verifier ... OAuth Verifier

### showTweet($id)

IDを指定した時にツイートの詳細を見ることができます。

* $id ... ツイートのid

### tweet($status,$in_reply_to_status_id=null)

ツイートする時に使えます。

* $status ... ツイート本文
* $in_reply_to_status_id (option) ... リプをする元のツイートのID

## 注意事項

* 一応ある程度形にはしたつもりですが作りかけです。どのような不具合が起こるか分かりません。
* 予告なしにこのレポジトリを削除する可能性があります。ご注意ください。
* 作成者が使用したPHPのバージョンは _PHP 7.1.7_ です。うまく動かない場合はPHPのバージョンをご確認ください。
* あくまで自分向けに作ったものであり，ransewhaleはこのライブラリを使用したことによる損害に対する責任を一切負いません。
