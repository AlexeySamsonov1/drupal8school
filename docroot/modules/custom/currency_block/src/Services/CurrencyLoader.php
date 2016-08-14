<?php

namespace Drupal\currency_block\Services;


class CurrencyLoader {

  const NBRB_URL = 'http://www.nbrb.by/Services/XmlExRates.aspx';

  //TODO Leave description.
  public function getCurrienciesFromNBRB() {
    $date = new \DateTime();
    $day = $date->format('z');

    $cid = 'currencyLoader:' . $day;

    $data = NULL;
    if ($cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {
      $client = \Drupal::httpClient();
      try {
        $request = $client->request('GET', self::NBRB_URL, [
          'headers' => [
            'Accept',
            'application/xml, text/xml'
          ]
        ]);
        $response = $request->getBody();
      }
      catch (\Exception $e) {
        watchdog_exception('currencyLoader', $e, $e->getMessage());
      }

      if ($request->getStatusCode() == 200) {
        $xml = $response->getContents();
        $data = $this->convertXmlToArray($xml);
        \Drupal::cache()->set($cid, $data);
      }
    }

    return $data;
  }

  /*
 * Helper function.
 */
  public function convertXmlToArray($xml) {
    $xml = simplexml_load_string($xml);
    $json = json_encode($xml);

    return json_decode($json, TRUE);
  }

  public function buildCurrencyList() {
    $currency_arr = $this->getCurrienciesFromNBRB();
    $currency_list = [];
    foreach ($currency_arr['Currency'] as $row) {
      $currency_list[$row['CharCode']] = [
        'Name' => $row['Name'],
        'CharCode' => $row['CharCode'],
        'Scale' => $row['Scale'],
        'Rate' => $row['Rate'],
      ];
    }

    return $currency_list;
  }
}
