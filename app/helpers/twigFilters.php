<?php

function dump($data){
  echo "<pre><code>";
  print_r($data);
  echo "</code></pre>";
}

function makeURL($requestURI) {
	$url = BASEURL.$requestURI;
	return $url;
}

$twig->addFunction('makeURL', new Twig_Function_Function('makeURL'));
$twig->addFunction('dump', new Twig_Function_Function('dump'));
//$twig->addFilter('email', new Twig_Filter_Function('obfuscateEmail'));
//$twig->addFilter('dash', new Twig_Filter_Function('spaceDash'));
//$twig->addFilter('dateF', new Twig_Filter_Function('formatDate'));
//$twig->addFilter('monthF', new Twig_Filter_Function('formatMonth'));
//$twig->addFilter('spaceDash', new Twig_Filter_Function('spaceDash'));
//$twig->addFilter('bool', new Twig_Filter_Function('boolF'));
