<?php
/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
require_once('LINEBotTiny.php');
require_once('LinebotConfig.php');

// create LineBot
$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    $messageTYPE = $event['message']['type'];
    
    switch ($event['type']) {                      
        case 'message':
            $message = $event['message'];                            
                                                 
            //detect keywords and get reply message
            $json = file_get_contents('https://spreadsheets.google.com/feeds/list/1uzIBH3rdZerzvyl9ddoGspIIBr9oMjgJHrwJBPwbnx0/od6/public/values?alt=json');
            $data = json_decode($json, true);

            foreach ($data['feed']['entry'] as $item) {
                $keywords = explode(',', $item['gsx$keywords']['$t']);
                foreach ($keywords as $keyword) {
                    if (mb_strpos($message['text'], $keyword) !== false) {
                        $reply_msg  = $item['gsx$replymessage']['$t'];
                    }
                }
            }
            //reply message
            switch ($messageTYPE) {
                case 'text': 
                $client->replyMessage(array(
                    'replyToken' => $event['replyToken'],
                    'messages' =>array(                       
                            array(
                                'type' => 'text',
                                'text' => $reply_msg,
                             ),                    
                    ),
                ));
                    break;                         
                default:
                    // Apache(error.log)
                    error_log("Unsupporeted message type: " . $messageTYPE);
                    break;
            }
            break;
        default:
            // Apache(error.log)
            error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
};