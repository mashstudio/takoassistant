'use strict';
 
const functions = require('firebase-functions');
const {WebhookClient} = require('dialogflow-fulfillment');
const {Card, Suggestion} = require('dialogflow-fulfillment');

const admin = require('firebase-admin');
admin.initializeApp();

const base_url = "https://<YOUR_SERVER_URL>/takoassistant/search.php";

//process.env.DEBUG = 'dialogflow:debug'; // enables lib debugging statements

//request
const request = require('request');
const get = (url) => new Promise((resolve, reject) => {
    request.get({
        url: url,
        json: true
    }, (error, res, json) => {
        if (error) {
            return reject(error);
        }
        resolve(json);
    });    
});


exports.dialogflowFirebaseFulfillment = functions.https.onRequest((request, response) => {
  const agent = new WebhookClient({ request, response });
  //console.log('Dialogflow Request headers: ' + JSON.stringify(request.headers));
  //console.log('Dialogflow Request body: ' + JSON.stringify(request.body));
 
  //アプリ起動時メッセージ
  function welcome(agent) {
    const text1 = "昭さん、";
    const text3 = "さあ凧上げにでかけましょう。";
    const text4 = "どこへ行きたいですか？それとも私に提案してほしいですか？";

    const text2Array = [
        '今日は凧日和ですよ。',
        'ゴロゴロしてないで、',
        '今日もテレビばかりみてませんか？',
        'お孫さんたちはお元気ですか？',
        'ドラ息子さんはお元気ですかね？',
        '昨日はよく眠れましたか？',
        '今日の体調はどうですか？',
        'しっかりご飯を食べてますか？',
        'ちゃんと薬を飲んでいますか？',
        '散歩したり体をうごかさないといけませんよ。',
        '今日もしっかりトイレに行きましたか？',
        '今日も暴れん坊将軍を見てましたか？',
        '今日も水戸黄門を見てましたか？',
        ];
    
    var text2Index = Math.floor(Math.random()*text2Array.length);
    var text2 = text2Array[text2Index];
    var talk = text1 + text2 + text3 + text4;
    
    agent.add(talk);

  }
  
  //〜〜へ行きたい
  function iWouldLikeToGoTo(agent) {
    var locationname = agent.parameters['any'];
    const url = encodeURI(base_url + "?mode=location&name="+locationname);

    return get(url).then(responsedJson => {

        var talk;
        var hourString;
        var windDirection;

        if (responsedJson.result === 'NotFoundLocation') {
            talk="その場所はわかりません。"+locationname+"で合っていますか？";
        }
        if (responsedJson.result === 'condition_bad') {
            hourString = convertAMPMHour(Number(responsedJson.alternative.hour));
            windDirection = windString(responsedJson.alternative.windDirectionJp);

            if (responsedJson.alternative === 'none') {
                talk=responsedJson.target.name+"のコンディションは良くありません。他におすすめできる場所も無いようです。";
            } else {
                talk=responsedJson.target.name+"のコンディションは良くありません。"
                +responsedJson.alternative.name+"はいかがでしょうか？"
                +hourString+"、"+windDirection+"、風速は"+responsedJson.alternative.wind+"メートルです。"
                +responsedJson.recommendKite+"を飛ばすのをおすすめします。";
            }
        }
        if (responsedJson.result === 'condition_normal') {
            hourString = convertAMPMHour(Number(responsedJson.target.hour));
            windDirection = windString(responsedJson.target.windDirectionJp);

            talk=responsedJson.target.name+"のコンディションはまずまずです。"
            +hourString+"、"+windDirection+"、風速は"+responsedJson.target.wind+"メートルです。"
            +responsedJson.recommendKite+"を飛ばすのをおすすめします。";
        }
        if (responsedJson.result === 'condition_good') {
            hourString = convertAMPMHour(Number(responsedJson.target.hour));
            windDirection = windString(responsedJson.target.windDirectionJp);

            talk=responsedJson.target.name+"のコンディションは良好です。"
            +hourString+"、"+windDirection+"、風速は"+responsedJson.target.wind+"メートルです。"
            +responsedJson.recommendKite+"を飛ばすのをおすすめします。";
        }        
        agent.add(talk);
    });
  }
  
  //〜〜を飛ばしたい
  function whichKiteYouWantToFly(agent) {
    var kitetype = agent.parameters['KiteType'];
    const url = encodeURI(base_url + "?mode=kite&type="+kitetype);

    return get(url).then(responsedJson => {
        // responsedJson に対する処理
        var talk;
        if (responsedJson.result === 'NotFoundKite') {
            talk = "カイトの情報が見つかりませんでした。カイトの名称は"+kitetype+"で合っていますか？";
        }
        if (responsedJson.result === 'NotFoundLocation') {
            talk = responsedJson.kite.type+"凧を飛ばせる適した場所は見つかりませんでした。";
        }
        if (responsedJson.result === "OK") {
            var hourString = convertAMPMHour(Number(responsedJson.location.hour));
            var windDirection = windString(responsedJson.location.windDirectionJp);

            talk = responsedJson.kite.type+"凧は、"
            +responsedJson.location.name+"で飛ばすのが良いでしょう。"
            +hourString
            +"、"+windDirection+"、風速は"+responsedJson.location.wind+"メートルです。";
        }
        agent.add(talk);
    });

  }
  
  //飛ばしたい凧ありますか？ーあります
  function doYouKnowWhichKiteYouWantToFly_YES(agent) {

      if (agent.parameters.KiteType.length === 0) {
          agent.add('どの凧を飛ばしたいですか？');
      } else {
          return whichKiteYouWantToFly(agent);
      }
  }

  //飛ばしたい凧ありますか？ーありません
  function doYouKnowWhichKiteYouWantToFly_NO(agent) {
    const url =  encodeURI(base_url + "?mode=best3");

    return get(url).then(responsedJson => {
        // responsedJson に対する処理
        var locationString = [];
        var kiteString;
        for(var i=0;i<responsedJson.length;i++) {
            var name = responsedJson[i].name;
            var hourString = convertAMPMHour(Number(responsedJson[i].hour));

            var windDirection = windString(responsedJson[i].windDirectionJp);
            locationString[i] = hourString+"、"+windDirection+"、風速"+responsedJson[i].wind+"メートル、"+responsedJson[i].name;
            if (i === 0) {
                kiteString = responsedJson[i].recommendKite;
            }
        }
        var locationCount = responsedJson.length;
        var talk = "";
        if (locationCount === 0) {
            talk = "現在、コンディションが悪いため、ご提案できる場所はありません";
        }
        if (locationCount === 1) {
            talk = "こちらが提案できるただ一つの場所です。"
            +locationString[0]+"が良いでしょう。"
            +kiteString+"を飛ばすのをおすすめします。";
        }
        if (locationCount === 2) {
            talk = "こちらが近場の"+locationCount+"つの場所です。まず、"
            +locationString[0]+"が良いでしょう。"
            +kiteString+"を飛ばすのをおすすめします。"
            +"次に、"
            +locationString[1]+"も良いでしょう。";
        }
        if (locationCount === 3) {
            talk = "こちらが近場の"+locationCount+"つの場所です。まず、"
            +locationString[0]+"が良いでしょう。"
            +kiteString+"を飛ばすのをおすすめします。"
            +"次に、"+locationString[1]
            +"と、"+locationString[2]+"も良いでしょう。";
        }
        agent.add(talk);
    });
  }
  
  //Convert 24 -> 12 hour
  function convertAMPMHour(hour) {
    var hour12 = hour % 12;
    var ampm = ["午前","午後"];
    var hourString = ampm[Math.floor(hour / 12)];
    return hourString + String(hour12) + '時';
  }
  
  //Wind string
  function windString(string) {
    return string + "の風";
  }

  // Run the proper function handler based on the matched Dialogflow intent name
  let intentMap = new Map();
  intentMap.set('Default Welcome Intent', welcome);
  intentMap.set('I would like to go to',iWouldLikeToGoTo);
  intentMap.set('Which kite you want to fly',whichKiteYouWantToFly);
  intentMap.set('Do you know which kite you want to fly - NO',doYouKnowWhichKiteYouWantToFly_NO);
  intentMap.set('Do you know which kite you want to fly - YES',doYouKnowWhichKiteYouWantToFly_YES);

  agent.handleRequest(intentMap);
});
