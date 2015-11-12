<?php

namespace App-zero\Repository;

class MandrillApi {

    function __construct($db) {
        $this->db = $db;
    }

    function getContent($info){

        $text = 'Gentile Cliente,<br><br>';

        if(!empty($info['message'])){
            $text .= 'Siamo lieti di informarla che il sig./sig.ra ' . $info['name'] . ' le ha mandato il seguente messaggio:<div style="border: solid 1px #eee; background-color: #f9f9f9; border-radius: 1px; min-height: 100px; margin: 5px 50px; padding: 5px 10px; font-style: italic; color: #555;">'.$info['message'].'</div><br>di seguito può trovare ulteriori dettagli: <div>';
        }else{
            $text .= 'Siamo lieti di informarla che ha ricevuto una nuova richiesta di contatto, di seguito può trovare i dettagli: <div>';
        }
        $text .= '<ul>';
        foreach ($info as $key => $value) {
            $text .= '<li>'.$key.': '.$value.'</li>';
        }
        $text .= '</ul>';
        $text .= '</div>';

        if(!empty($info['email'])){
        $text .= 'Può rispondere all\'indirizzo mail '.$info['email'];
        }
        if(!empty($info['email'])&&!empty($info['phone'])){
        $text .= ' o direttamente al numero di telefono: <strong>'.$info['phone'].'</strong>';
        }
        if(empty($info['email'])&&!empty($info['phone'])){
        $text .= 'Può rispondere  direttamente al numero di telefono: <strong>'.$info['phone'].'</strong>';
        }
        $text .='<br><br>Cordiali saluti da Instilla.';

        $template_content = array(
            array(
                'name' => 'text',
                'content' => $text,
            )
        );

        return $template_content;
    }

    function getContentForCustomer($info){
        $text ='Gentile cliente,<br><br>Siamo lieti di comunicarle che la sua richiesta è stata ricevuta, non appena possibile provvederemo a risponderle,<br><br>un Cordiale saluto da <strong>'.$info['domain'].'!</strong>';
        $template_content = array(
            array(
                'name' => 'text',
                'content' => $text,
            )
        );
        return $template_content;
    }

    function sendMessage($post){
        $message = array(
               'subject' => 'Nuova richiesta Informazioni tramite form di contatto',
               'from_email' => $post['email'],
               'from_name' => $post['name'],
               'to' => array(
                   array(
                       'email' => $post['mailto'],
                       'name' => $post['mailto'],
                       'type' => 'to'
                   )
               ),
               'important' => false,
               'track_opens' => null,
               'track_clicks' => null,
               'auto_text' => null,
               'auto_html' => null,
               'inline_css' => null,
               'url_strip_qs' => null,
               'preserve_recipients' => null,
               'view_content_link' => null,
               'tracking_domain' => null,
               'signing_domain' => null,
               'return_path_domain' => null,
               'merge' => true,
               'merge_language' => 'mailchimp',
               'global_merge_vars' => array(
                   array(
                       'name' => 'merge1',
                       'content' => 'merge1 content'
                   )
               ),
               'merge_vars' => array(
                   array(
                       'rcpt' => 'recipient.email@example.com',
                       'vars' => array(
                           array(
                               'name' => 'merge2',
                               'content' => 'merge2 content'
                           )
                       )
                   )
               ),
           );
           return $message;
       }
}
?>
