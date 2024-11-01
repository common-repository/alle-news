<?php
/*
Plugin Name: Alle News
Plugin URI: http://www.alle-news.com/
Description: Anzeige aktueller deutschsprachiger Nachrichten aus Deutschland, &Ouml;sterreich und der Schweiz.
Version:  1.0
Author: Dirk Moosbach
Author URI: http://www.alle-news.com/
*/

/*  Copyright 2009 Dirk Moosbach
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$AlleNewsVersion = '1.0';
$AlleNewsDefault['Title'] = "Aktuelle Nachrichten";
$AlleNewsDefault['Quantity'] = 3;
$AlleNewsDefault['MaxText'] = 100;


function AlleNewsInit() {
     if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') ) {
		return;
     }
     register_sidebar_widget('Alle News', 'AlleNewsWidget');
     register_widget_control('Alle News', 'AlleNewsControl');
}

add_action('plugins_loaded', 'AlleNewsInit');


function LimitText($text, $limit) {
     $text = trim($text);
     if( strlen($text) > $limit ){
          $text = substr($text, 0, $limit);
          $pos = strrpos($text, " ");
          if ($pos !== false) { 
               $text = substr($text, 0, $pos)."...";
          } else {
               $text = "";
          }
     }
     return $text;
}

function AlleNewsVerify() {
     global $AlleNewsDefault;
     global $AlleNewsOptions;
     
     if( $AlleNewsOptions['Title'] == '' ) {
          $AlleNewsOptions['Title'] = $AlleNewsDefault['Title'];
     }
     if( $AlleNewsOptions['Quantity'] <= 0 || $AlleNewsOptions['Quantity'] > 10 ) {
          $AlleNewsOptions['Quantity'] = $AlleNewsDefault['Quantity'];
     }
     if( $AlleNewsOptions['MaxText'] < 0 || $AlleNewsOptions['MaxText'] > 200 || $AlleNewsOptions['MaxText'] == "" ){
          $AlleNewsOptions['MaxText'] = $AlleNewsDefault['MaxText']; 
     }
}

function AlleNewsWidget( $Args ) {
     global $AlleNewsVersion;
     global $AlleNewsDefault;
     global $AlleNewsOptions;
     
     $AlleNewsOptions = get_option('AlleNews');
          
     $xml_file = simplexml_load_file("http://www.alle-news.com/news.xml");
     foreach($xml_file->item as $item) {  
          $AlleNewsTitle[] = trim($item->titel); 
          $AlleNewsText[] = trim($item->text); 
          $AlleNewsSource[] = trim($item->source); 
          $AlleNewsDatetime[] = trim($item->datetime); 
          $AlleNewsURL[] = trim($item->url);
     }
     
     AlleNewsVerify();
          
     echo  $Args['before_widget'].'<a href="http://www.alle-news.com/" target="_blank">Alle News</a><br />'.$Args['before_title'].$AlleNewsOptions['Title'].$Args['after_title'];
     echo '<ul>';
     for( $i=0; $i<min(count($AlleNewsTitle), $AlleNewsOptions['Quantity']); $i++) {
          $snippet = LimitText($AlleNewsText[$i], $AlleNewsOptions['MaxText']);
          echo '<li><a href="'.$AlleNewsURL[$i].'" target="_blank">'.$AlleNewsTitle[$i].'</a>';
          if( $snippet != "" ) { echo '<br />'.$snippet; }
          echo '<small><br />Quelle: '.$AlleNewsSource[$i].'</small></li>';
     }
     echo '</ul>';
     echo $Args['after_widget'];
}

function AlleNewsControl() {
     global $AlleNewsModusValues;
     global $AlleNewsOptions;

     $AlleNewsVar = array();
     $AlleNewsOptions = get_option('AlleNews');
     
     AlleNewsVerify();
          
     if(isset($_POST['AlleNewsSubmit'])) {
          $AlleNewsVar['Title'] = mysql_escape_string(htmlspecialchars($_POST['AlleNewsTitle']));
          $AlleNewsVar['Quantity'] = intval($_POST['AlleNewsQuantity']);
          $AlleNewsVar['AlleNewsMaxText'] = intval($_POST['AlleNewsMaxText']);
          
          AlleNewsVerify();
          
          update_option('AlleNews', $AlleNewsVar);
     }
     
     echo '<div><table border="0" cellpadding="0" cellspacing="0">';
     echo '<tr><td><label for="AlleNewsTitle">Titel:</label></td><td><input type="text" id="AlleNewsTitle" name="AlleNewsTitle" style="width:140px" value="'.$AlleNewsOptions['Title'].'" /></td></tr>';
     echo '<tr><td><label for="AlleNewsQuantity">Meldungen (1-10):</label></td><td><input type="text" id="AlleNewsQuantity" name="AlleNewsQuantity" value="'.$AlleNewsOptions['Quantity'].'" style="width:30px;" /></td></tr>';
     echo '<tr><td><label for="AlleNewsMaxText">Zeichen im Text (0-200):</label></td><td><input type="text" id="AlleNewsMaxText" name="AlleNewsMaxText" value="'.$AlleNewsOptions['MaxText'].'" style="width:30px;" /></td></tr>';
     echo '<input type="hidden" name="AlleNewsSubmit" id="AlleNewsSubmit" value="true" />';
     echo '</table></div>';
}
?>