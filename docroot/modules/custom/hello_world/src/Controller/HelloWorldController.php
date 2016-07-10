<?php

namespace Drupal\hello_world\Controller;

use Symfony\Component\HttpFoundation\Response;

class HelloWorldController {
  public function index()
  {

    return new Response('Hello World!');
  }
}
