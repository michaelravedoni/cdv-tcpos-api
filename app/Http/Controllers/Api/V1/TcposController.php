<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Article;

class TcposController extends Controller
{

    /**
     * Get all DB data from TCPOS WCF.
     */
    public function getDB()
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
        CURLOPT_PORT => "10306",
        CURLOPT_URL => env('TCPOS_API_WCF_URL'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"  xmlns:wsu=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd\" xmlns:msc=\"http://schemas.microsoft.com/ws/2005/12/wsdl/contract\" xmlns:tns=\"http://tempuri.org/\"><soap:Body><GetDB xmlns=\"http://tempuri.org/\"><xmlRequest>\n&lt;DB&gt;\n&lt;PRICELEVELS&gt;\n&lt;CODE&gt;2&lt;/CODE&gt;\n&lt;CODE&gt;5&lt;/CODE&gt;\n&lt;CODE&gt;13&lt;/CODE&gt;\n&lt;/PRICELEVELS&gt;\n&lt;/DB&gt;\n</xmlRequest></GetDB></soap:Body></soap:Envelope>",
        CURLOPT_HTTPHEADER => [
            "Content-Type: text/xml; charset=utf-8",
            'SOAPAction: "http://tempuri.org/IWebShop/GetDB"'
        ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
            $xmlResponse = str_replace(
                [
                    '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><GetDBResponse xmlns="http://tempuri.org/">',
                    '</GetDBResponse></s:Body></s:Envelope>'
                ]
                , '', html_entity_decode($response));
            $xmlParsed = simplexml_load_string($xmlResponse);
            $jsonEncoded = json_encode($xmlParsed);
            $jsonDecoded = json_decode($jsonEncoded,TRUE);
            $data = data_get($jsonDecoded, 'DB');
            return $data;
        }
    }

    /**
     * Get all TCPOS articles.
     */
    public function getArticles()
    {
        return data_get($this->getDB(), 'ARTICLES.ARTICLE');
    }

    /**
     * Import articles in database.
     */
    public function importArticles()
    {
        Article::truncate();
        foreach ($this->getArticles() as $key => $tcposArticle) {

            $article = (object) $tcposArticle;

            $articleCreate = new Article;
            $articleCreate->name = $article->DESCRIPTION;
            $articleCreate->category = $articleCreate->setArticleCategory(data_get($article, 'NOTES-2'));
            $articleCreate->priceLevelCodes = data_get($article, 'PRICELEVELS.PRICELEVEL');
            //$articleCreate->minQuantity = config('cdv.default_product_min_quantity');
            //$articleCreate->maxQuantity = $article->articleOrder ?? config('cdv.default_product_max_quantity');

            //$articleCreate->weight = $article->preparationWeight ?? 0;
            //$articleCreate->vatInPercent = data_get($article, 'vats.vatindex1', 'vats.vatindex2');

            //$articleCreate->sync_action = 'update';
            //$articleCreate->hash = $tcposArticleHash;

            //$articleCreate->description = $article->wondDescription;
            //$articleCreate->articleOrder = $article->articleOrder;
            //$articleCreate->isAddition = $article->isAddition;
            //$articleCreate->measureUnitId = $article->measureUnitId;
            $articleCreate->printoutNotes = data_get($article, 'PRINTOUT-NOTES');
            $articleCreate->notes1 = is_array(data_get($article, 'NOTES-1')) ? json_encode(data_get($article, 'NOTES-1')) : data_get($article, 'NOTES-1');
            $articleCreate->notes2 = data_get($article, 'NOTES-2');
            $articleCreate->notes3 = data_get($article, 'NOTES-3');
            $articleCreate->groupACode = data_get($article, 'GROUP-A-CODE');
            $articleCreate->groupBCode = data_get($article, 'GROUP-B-CODE');
            $articleCreate->groupCCode = data_get($article, 'GROUP-C-CODE');
            $articleCreate->groupDCode = data_get($article, 'GROUP-D-CODE');

            //$articleCreate->_tcposId = $article->id;
            $articleCreate->_tcposCode = $article->CODE;
            $articleCreate->save();
        }
    }

    /**
     * Show wine menu.
     */
    public function showWineMenu()
    {
        $articles = [];
        foreach (Article::where('category', 'wine')->where('_tcposCode', '<', 200000)->get() as $article) {
            $articles[] = [
                'name' => $article->name,
                'description' => data_get($article->tcposProduct, 'description'),
                'prices' => [
                    'takeAway' => [
                        'priceLevelCode' => 2,
                        'price' => data_get($article->tcposProduct, 'pricesRelations.0.price', data_get($article->priceLevelCodes, '0.PRICE')),
                        //'vatInPercent' => $article->vatInPercent,
                    ],
                    'onSite' => [
                        'priceLevelCode' => 5,
                        'price' => data_get($article->tcposProduct, 'pricesRelations.1.price', data_get($article->priceLevelCodes, '1.PRICE')),
                        //'vatInPercent' => $article->vatInPercent,
                    ],
                    'online' => [
                        'priceLevelCode' => 13,
                        'price' => data_get($article->tcposProduct, 'pricesRelations.2.price', data_get($article->priceLevelCodes, '2.PRICE')),
                        //'vatInPercent' => $article->vatInPercent,
                    ]
                ],
                'attributes' => $article->attributesArray(),
                'category' => $article->category,
                'code' => $article->_tcposCode,
            ];
        }
        return $articles;
    }
}
