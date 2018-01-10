<?php
require 'inc.includes.php';

$pdo = PDODefault::getInstance();

$pdo->prepare("INSERT INTO ic_topic
(topic_id, topic, url) 
VALUES
(1, 'Esporte', 'esporte'),
(2, 'Cultura', 'cultura'),
(3, 'Lazer', 'lazer'),
(4, 'Educação', 'educacao'),
(5, 'Mobilidade urbana', 'mobilidade-urbana'),
(6, 'Espaço público', 'espaco-publico');")->execute();
?>