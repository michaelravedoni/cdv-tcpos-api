<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Price;
use App\Models\Attribute;

class AttributeController extends Controller
{

    /**
     * Show all attributes.
     */
    public function index()
    {
        return Attribute::all();
    }

    /**
     * Show the attribute by id raw.
     */
    public function show($id)
    {
        return Attribute::where('_tcposId', $id)->get();
    }

    /**
     * Import attributes in database.
     */
    public function importAttributes()
    {
        $begin = microtime(true);

        Attribute::truncate();

        foreach ($this->getAttributes() as $key => $attributeRaw) {

            $attribute = (object) $attributeRaw;

            $attributeCreate = new Attribute;
            $attributeCreate->name = $attribute->DESCRIPTION;
            $attributeCreate->notes1 = $attribute->NOTES1;
            $attributeCreate->notes2 = $attribute->NOTES2;
            $attributeCreate->notes3 = $attribute->NOTES3;
            $attributeCreate->_tcposId = $attribute->ID;
            $attributeCreate->_tcposCode = $attribute->CODE;
            $attributeCreate->save();

            if ($key == 2) {
                $brake;
            }
        }
        
        $end = microtime(true) - $begin;

        return response()->json([
            'message' => 'imported',
            'time' => $end,
            'count' => Attribute::all()->count(),
        ]);
    }

    /**
     * Show the attributes raw.
     */
    public function getAttributes()
    {
        $req = Http::withOptions([
            'verify' => false,
        ])->get(env('TCPOS_API_CDV_URL').'/getallgroups');
        $response = $req->json();
        $data = data_get($response, 'GROUPS');
        
        $dataFlat = json_decode(
            '{
                "GROUPS": [
                  {
                    "ID": "824",
                    "CODE": "33020",
                    "DESCRIPTION": "Cotisations",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "823",
                    "CODE": "1031",
                    "DESCRIPTION": "Shop Online",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "822",
                    "CODE": "366",
                    "DESCRIPTION": "Cidre",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "821",
                    "CODE": "113",
                    "DESCRIPTION": "The Alps – Domaines Rouvinez SA",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "820",
                    "CODE": "48",
                    "DESCRIPTION": "Brasserie La Marmotte",
                    "NOTES1": "www.brasserie-la-marmotte.ch",
                    "NOTES2": "brasserie-la-marmotte@bluewin.ch",
                    "NOTES3": "027.481.34.14",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "819",
                    "CODE": "415",
                    "DESCRIPTION": "33 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "818",
                    "CODE": "365",
                    "DESCRIPTION": "Bière",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "792",
                    "CODE": "546",
                    "DESCRIPTION": "Monthey",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "791",
                    "CODE": "47",
                    "DESCRIPTION": "Alata",
                    "NOTES1": "www.alata.love",
                    "NOTES2": "contact@alata.love",
                    "NOTES3": "079.796.71.58",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "790",
                    "CODE": "45",
                    "DESCRIPTION": "I. & S. Kellenberger, Vin d\'œuvre",
                    "NOTES1": "www.vindoeuvre.ch",
                    "NOTES2": "info@vindoeuvre.ch",
                    "NOTES3": "027.473.38.38",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "789",
                    "CODE": "545",
                    "DESCRIPTION": "Leuk Stadt",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "787",
                    "CODE": "364",
                    "DESCRIPTION": "Petit Verdot",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "786",
                    "CODE": "363",
                    "DESCRIPTION": "Grosse Arvine",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "785",
                    "CODE": "362",
                    "DESCRIPTION": "Chambourcin",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "784",
                    "CODE": "360",
                    "DESCRIPTION": "Completer",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "783",
                    "CODE": "414",
                    "DESCRIPTION": "2 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "782",
                    "CODE": "44",
                    "DESCRIPTION": "NG liqueurs",
                    "NOTES1": "",
                    "NOTES2": "info@ng-liqueurs.ch",
                    "NOTES3": "078 876 69 05",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "781",
                    "CODE": "544",
                    "DESCRIPTION": "Conthey",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "780",
                    "CODE": "361",
                    "DESCRIPTION": "Sémillon",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "778",
                    "CODE": "358",
                    "DESCRIPTION": "Eyholzer Roter",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "777",
                    "CODE": "357",
                    "DESCRIPTION": "Divico",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "776",
                    "CODE": "356",
                    "DESCRIPTION": "Fumin",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "775",
                    "CODE": "355",
                    "DESCRIPTION": "Malbec",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "774",
                    "CODE": "43",
                    "DESCRIPTION": "Domaine de la Rameau",
                    "NOTES1": "www.rameau.ch",
                    "NOTES2": "info@rameau.ch",
                    "NOTES3": "027 306 12 52",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "772",
                    "CODE": "413",
                    "DESCRIPTION": "4 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "771",
                    "CODE": "42",
                    "DESCRIPTION": "La Petite Savièsanne",
                    "NOTES1": "",
                    "NOTES2": "gael.roten@romandie.com",
                    "NOTES3": "079.961.82.43",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "770",
                    "CODE": "19",
                    "DESCRIPTION": "Grands Crus du Valais",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "769",
                    "CODE": "15",
                    "DESCRIPTION": "Cave Petite Vertu",
                    "NOTES1": "www.petite–vertu.ch",
                    "NOTES2": "info@petite–vertu.ch",
                    "NOTES3": "027.306.10.22",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "768",
                    "CODE": "543",
                    "DESCRIPTION": "Raron",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "767",
                    "CODE": "542",
                    "DESCRIPTION": "Bramois",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "766",
                    "CODE": "41",
                    "DESCRIPTION": "Chai du Baron",
                    "NOTES1": "www.chaidubaron.ch",
                    "NOTES2": "chai@chaidubaron.ch",
                    "NOTES3": "027.203.40.60",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "765",
                    "CODE": "37",
                    "DESCRIPTION": "Cave Valentina Andrei",
                    "NOTES1": "www.valentinaandrei.ch",
                    "NOTES2": "valentina.andrei@bluewin.ch",
                    "NOTES3": "079.947.03.86",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "764",
                    "CODE": "36",
                    "DESCRIPTION": "Cave le Tambourin",
                    "NOTES1": "www.letambourin.ch",
                    "NOTES2": "info@letambourin.ch",
                    "NOTES3": "078.842.94.58",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "763",
                    "CODE": "28",
                    "DESCRIPTION": "Weingut Cipolla",
                    "NOTES1": "www.romain–cipolla.ch",
                    "NOTES2": "info@weingut–cipolla.ch",
                    "NOTES3": "079.201.81.31",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "761",
                    "CODE": "354",
                    "DESCRIPTION": "Durize",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "760",
                    "CODE": "353",
                    "DESCRIPTION": "Carminoir",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "759",
                    "CODE": "352",
                    "DESCRIPTION": "Galotta",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "758",
                    "CODE": "25",
                    "DESCRIPTION": "Cave des Amandiers",
                    "NOTES1": "www.cavedesamandiers.ch",
                    "NOTES2": "info@cavedesamandiers.ch",
                    "NOTES3": "027.746.22.01",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "757",
                    "CODE": "27",
                    "DESCRIPTION": "Cave Caloz",
                    "NOTES1": "www.cavecaloz.ch",
                    "NOTES2": "sandrine.caloz@bluewin.ch",
                    "NOTES3": "027.455.22.06",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "756",
                    "CODE": "24",
                    "DESCRIPTION": "Cave le Vidomne",
                    "NOTES1": "www.levidomne.ch",
                    "NOTES2": "meinradgaillard@bluewin.ch",
                    "NOTES3": "027.306.27.80",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "755",
                    "CODE": "541",
                    "DESCRIPTION": "La Tour-de-Peilz",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "754",
                    "CODE": "540",
                    "DESCRIPTION": "Martigny-Croix",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "747",
                    "CODE": "33017",
                    "DESCRIPTION": "Produits location",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "744",
                    "CODE": "33019",
                    "DESCRIPTION": "Produits divers",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "743",
                    "CODE": "33016",
                    "DESCRIPTION": "Produits  cigares",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "742",
                    "CODE": "33015",
                    "DESCRIPTION": "Produits  café et thés",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "741",
                    "CODE": "33014",
                    "DESCRIPTION": "Produits minérales et jus",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "739",
                    "CODE": "33011",
                    "DESCRIPTION": "Produits du vin",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "738",
                    "CODE": "33012",
                    "DESCRIPTION": "Produits de la bière",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "737",
                    "CODE": "33010",
                    "DESCRIPTION": "Produit de la cuisine",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "735",
                    "CODE": "350",
                    "DESCRIPTION": "Aligoté",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "732",
                    "CODE": "347",
                    "DESCRIPTION": "Lafnetscha",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "731",
                    "CODE": "346",
                    "DESCRIPTION": "Dôle Blanche",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "730",
                    "CODE": "411",
                    "DESCRIPTION": "5 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "729",
                    "CODE": "410",
                    "DESCRIPTION": "10 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "727",
                    "CODE": "539",
                    "DESCRIPTION": "Valais",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "726",
                    "CODE": "538",
                    "DESCRIPTION": "Pont-de-la-Morge",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "725",
                    "CODE": "537",
                    "DESCRIPTION": "Visperterminen",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "723",
                    "CODE": "535",
                    "DESCRIPTION": "Ardon",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "722",
                    "CODE": "534",
                    "DESCRIPTION": "Grimisuat",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "721",
                    "CODE": "533",
                    "DESCRIPTION": "Uvrier",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "720",
                    "CODE": "532",
                    "DESCRIPTION": "Susten-Leuk",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "719",
                    "CODE": "531",
                    "DESCRIPTION": "Riddes",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "718",
                    "CODE": "530",
                    "DESCRIPTION": "Noës",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "717",
                    "CODE": "529",
                    "DESCRIPTION": "Chalais",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "715",
                    "CODE": "527",
                    "DESCRIPTION": "St-German",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "713",
                    "CODE": "525",
                    "DESCRIPTION": "Choëx",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "712",
                    "CODE": "524",
                    "DESCRIPTION": "Randogne",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "711",
                    "CODE": "523",
                    "DESCRIPTION": "St-Pierre-de-Clages",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "710",
                    "CODE": "522",
                    "DESCRIPTION": "Chippis",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "709",
                    "CODE": "521",
                    "DESCRIPTION": "Varen",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "708",
                    "CODE": "520",
                    "DESCRIPTION": "Visp",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "707",
                    "CODE": "519",
                    "DESCRIPTION": "Martigny",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "706",
                    "CODE": "518",
                    "DESCRIPTION": "Corin–sur–Sierre",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "705",
                    "CODE": "517",
                    "DESCRIPTION": "Leytron",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "704",
                    "CODE": "516",
                    "DESCRIPTION": "Savièse",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "703",
                    "CODE": "515",
                    "DESCRIPTION": "Flanthey",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "702",
                    "CODE": "514",
                    "DESCRIPTION": "Veyras",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "700",
                    "CODE": "512",
                    "DESCRIPTION": "Vétroz",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "699",
                    "CODE": "511",
                    "DESCRIPTION": "Saillon",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "698",
                    "CODE": "510",
                    "DESCRIPTION": "Champlan",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "697",
                    "CODE": "509",
                    "DESCRIPTION": "Sion",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "695",
                    "CODE": "507",
                    "DESCRIPTION": "Venthône",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "694",
                    "CODE": "506",
                    "DESCRIPTION": "Sierre",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "693",
                    "CODE": "505",
                    "DESCRIPTION": "Miège",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "692",
                    "CODE": "504",
                    "DESCRIPTION": "Saxon",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "691",
                    "CODE": "503",
                    "DESCRIPTION": "St-Léonard",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "690",
                    "CODE": "502",
                    "DESCRIPTION": "Salgesch",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "689",
                    "CODE": "501",
                    "DESCRIPTION": "Chamoson",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "688",
                    "CODE": "500",
                    "DESCRIPTION": "Fully",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "687",
                    "CODE": "409",
                    "DESCRIPTION": "100 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "685",
                    "CODE": "407",
                    "DESCRIPTION": "150 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "684",
                    "CODE": "406",
                    "DESCRIPTION": "20 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "683",
                    "CODE": "405",
                    "DESCRIPTION": "3 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "682",
                    "CODE": "404",
                    "DESCRIPTION": "35 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "681",
                    "CODE": "403",
                    "DESCRIPTION": "70 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "680",
                    "CODE": "402",
                    "DESCRIPTION": "50 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "679",
                    "CODE": "401",
                    "DESCRIPTION": "75 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "678",
                    "CODE": "400",
                    "DESCRIPTION": "37.5 cl",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "676",
                    "CODE": "344",
                    "DESCRIPTION": "Altesse",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "675",
                    "CODE": "343",
                    "DESCRIPTION": "Mondeuse",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "672",
                    "CODE": "340",
                    "DESCRIPTION": "Chenin Blanc",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "670",
                    "CODE": "338",
                    "DESCRIPTION": "Régent",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "669",
                    "CODE": "337",
                    "DESCRIPTION": "Sauvignon Blanc",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "668",
                    "CODE": "336",
                    "DESCRIPTION": "Riesling",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "667",
                    "CODE": "335",
                    "DESCRIPTION": "Gwäss",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "666",
                    "CODE": "334",
                    "DESCRIPTION": "Himbertscha",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "665",
                    "CODE": "333",
                    "DESCRIPTION": "Rèze",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "664",
                    "CODE": "332",
                    "DESCRIPTION": "Gamaret",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "663",
                    "CODE": "331",
                    "DESCRIPTION": "Dôle",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "662",
                    "CODE": "330",
                    "DESCRIPTION": "Rosé",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "661",
                    "CODE": "329",
                    "DESCRIPTION": "Roussanne",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "660",
                    "CODE": "328",
                    "DESCRIPTION": "Muscat",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "659",
                    "CODE": "327",
                    "DESCRIPTION": "Pinot Blanc",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "658",
                    "CODE": "326",
                    "DESCRIPTION": "Gewurztraminer",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "657",
                    "CODE": "325",
                    "DESCRIPTION": "Grain Noble",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "656",
                    "CODE": "324",
                    "DESCRIPTION": "Chardonnay",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "655",
                    "CODE": "323",
                    "DESCRIPTION": "Pinot Gris/Malvoisie",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "654",
                    "CODE": "322",
                    "DESCRIPTION": "Assemblage Blanc",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "653",
                    "CODE": "321",
                    "DESCRIPTION": "Amigne",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "652",
                    "CODE": "320",
                    "DESCRIPTION": "Humagne Rouge",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "651",
                    "CODE": "319",
                    "DESCRIPTION": "Charmont",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "650",
                    "CODE": "318",
                    "DESCRIPTION": "Paien/Heida/Savagnin Blanc",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "649",
                    "CODE": "317",
                    "DESCRIPTION": "Merlot",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "648",
                    "CODE": "316",
                    "DESCRIPTION": "Cornalin",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "647",
                    "CODE": "315",
                    "DESCRIPTION": "Viognier",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "646",
                    "CODE": "314",
                    "DESCRIPTION": "Syrah",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "645",
                    "CODE": "313",
                    "DESCRIPTION": "Assemblage Rouge",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "644",
                    "CODE": "312",
                    "DESCRIPTION": "Petite Arvine",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "643",
                    "CODE": "311",
                    "DESCRIPTION": "Diolinoir",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "642",
                    "CODE": "310",
                    "DESCRIPTION": "Humagne Blanche",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "641",
                    "CODE": "309",
                    "DESCRIPTION": "Marsanne/Ermitage",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "640",
                    "CODE": "308",
                    "DESCRIPTION": "Pinot Noir",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "639",
                    "CODE": "307",
                    "DESCRIPTION": "Cabernet Franc",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "638",
                    "CODE": "306",
                    "DESCRIPTION": "Gamay",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "637",
                    "CODE": "305",
                    "DESCRIPTION": "Œil-de-Perdrix",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "636",
                    "CODE": "304",
                    "DESCRIPTION": "Johannisberg",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "635",
                    "CODE": "302",
                    "DESCRIPTION": "Fendant/Chasselas",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "634",
                    "CODE": "301",
                    "DESCRIPTION": "Liqueur",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "633",
                    "CODE": "300",
                    "DESCRIPTION": "Eau–de–vie",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "632",
                    "CODE": "136",
                    "DESCRIPTION": "Domaines Chevaliers",
                    "NOTES1": "www.chevaliers.ch",
                    "NOTES2": "info@chevaliers.ch",
                    "NOTES3": "027.455.28.28",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "631",
                    "CODE": "34",
                    "DESCRIPTION": "Thierry Constantin",
                    "NOTES1": "www.thierryconstantin.ch",
                    "NOTES2": "info@thierryconstantin.ch",
                    "NOTES3": "027.346.61.21",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "630",
                    "CODE": "134",
                    "DESCRIPTION": "St-Jodern Kellerei",
                    "NOTES1": "www.jodernkellerei.ch",
                    "NOTES2": "info@jodernkellerei.ch",
                    "NOTES3": "027.948.43.48",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "629",
                    "CODE": "75",
                    "DESCRIPTION": "Simon Maye & Fils",
                    "NOTES1": "www.simonmaye.ch",
                    "NOTES2": "simon.maye@teltron.ch",
                    "NOTES3": "027.306.41.81",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "628",
                    "CODE": "70",
                    "DESCRIPTION": "Sélection Excelsus",
                    "NOTES1": "www.selectionexcelsus.ch",
                    "NOTES2": "excelsus@teltron.ch",
                    "NOTES3": "027.306.14.00",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "626",
                    "CODE": "135",
                    "DESCRIPTION": "Rostal Grand-St-Bernard SA",
                    "NOTES1": "www.rostal.ch",
                    "NOTES2": "info@rostal.ch",
                    "NOTES3": "027.722.20.36",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "625",
                    "CODE": "63",
                    "DESCRIPTION": "Maison Gilliard SA",
                    "NOTES1": "www.gilliard.ch",
                    "NOTES2": "maison@gilliard.ch",
                    "NOTES3": "027.329.89.29",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "624",
                    "CODE": "64",
                    "DESCRIPTION": "Rives du Bisse - G. Delaloye & Fils SA",
                    "NOTES1": "www.rivesdubisse.ch",
                    "NOTES2": "info@rivesdubisse.ch",
                    "NOTES3": "027.306.13.15",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "623",
                    "CODE": "144",
                    "DESCRIPTION": "Réserve du Château",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "622",
                    "CODE": "98",
                    "DESCRIPTION": "Les Fils de René Favre SA",
                    "NOTES1": "www.petite–arvine.com",
                    "NOTES2": "johnetmikevin@petite–arvine.com",
                    "NOTES3": "027.306.39.21",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "621",
                    "CODE": "92",
                    "DESCRIPTION": "Régence Balavaud",
                    "NOTES1": "www.regence.ch",
                    "NOTES2": "info@regence.ch",
                    "NOTES3": "058.434.48.18",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "620",
                    "CODE": "139",
                    "DESCRIPTION": "Provins SA",
                    "NOTES1": "www.provins.ch",
                    "NOTES2": "provins@provins.ch",
                    "NOTES3": "058.434.48.18",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "619",
                    "CODE": "133",
                    "DESCRIPTION": "Pierre-Maurice Carruzzo",
                    "NOTES1": "www.pmcarruzzo.ch",
                    "NOTES2": "info@pmcarruzzo.ch",
                    "NOTES3": "027.306.37.56",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "618",
                    "CODE": "66",
                    "DESCRIPTION": "Philippoz Frères",
                    "NOTES1": "www.philippoz–freres.ch",
                    "NOTES2": "r.philippoz@bluewin.ch",
                    "NOTES3": "079.301.06.68",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "617",
                    "CODE": "124",
                    "DESCRIPTION": "Philippe Varone Vins SA",
                    "NOTES1": "www.varone.ch",
                    "NOTES2": "info@varone.ch",
                    "NOTES3": "027.203.56.83",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "615",
                    "CODE": "46",
                    "DESCRIPTION": "Nouveau St-Clément SA",
                    "NOTES1": "www.cavelamon.ch",
                    "NOTES2": "clamon@cavelamon.ch",
                    "NOTES3": "027.458.48.58",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "614",
                    "CODE": "100",
                    "DESCRIPTION": "Nouveau Salquenen AG",
                    "NOTES1": "www.mathier.com",
                    "NOTES2": "info@mathier.com",
                    "NOTES3": "027.455.75.75",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "613",
                    "CODE": "81",
                    "DESCRIPTION": "Maurice Zufferey",
                    "NOTES1": "www.mauricezufferey.ch",
                    "NOTES2": "contact@mauricezufferey.ch",
                    "NOTES3": "027.455.47.16",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "612",
                    "CODE": "102",
                    "DESCRIPTION": "Maurice Gay SA",
                    "NOTES1": "027.455.11.50",
                    "NOTES2": "info@mauricegay.ch",
                    "NOTES3": "www.mauricegay.ch",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "611",
                    "CODE": "96",
                    "DESCRIPTION": "Marie-Bernard Gillioz Praz",
                    "NOTES1": "www.mbgillioz.ch",
                    "NOTES2": "mbgillioz@bluewin.ch",
                    "NOTES3": "027.398.15.44",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "610",
                    "CODE": "3",
                    "DESCRIPTION": "L Orpailleur",
                    "NOTES1": "www.orpailleur.ch",
                    "NOTES2": "info@orpailleur.ch",
                    "NOTES3": "027.203.04.46",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "609",
                    "CODE": "10",
                    "DESCRIPTION": "Leukersonne Damian Seewer AG",
                    "NOTES1": "www.leukersonne.ch",
                    "NOTES2": "info@leukersonne.ch",
                    "NOTES3": "027.473.20.35",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "608",
                    "CODE": "68",
                    "DESCRIPTION": "Les Fils Maye SA",
                    "NOTES1": "www.maye.ch",
                    "NOTES2": "info@maye.ch",
                    "NOTES3": "027.305.15.00",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "607",
                    "CODE": "86",
                    "DESCRIPTION": "Les Fils de Charles Favre SA",
                    "NOTES1": "www.favre–vins.ch",
                    "NOTES2": "info@favre–vins.ch",
                    "NOTES3": "027.327.50.50",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "604",
                    "CODE": "59",
                    "DESCRIPTION": "La Cave à Polyte SA",
                    "NOTES1": "www.polyte.ch",
                    "NOTES2": "info@polyte.ch",
                    "NOTES3": "079.220.35.11",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "603",
                    "CODE": "131",
                    "DESCRIPTION": "Kohli Olivier & Charly",
                    "NOTES1": "",
                    "NOTES2": "charly.kohli@bluewin.ch",
                    "NOTES3": "027.744.25.24",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "602",
                    "CODE": "69",
                    "DESCRIPTION": "Domaine des Crêtes",
                    "NOTES1": "www.domainedescretes.ch",
                    "NOTES2": "info@domainedescretes.ch",
                    "NOTES3": "027.458.26.49",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "601",
                    "CODE": "73",
                    "DESCRIPTION": "Jean-René Germanier SA",
                    "NOTES1": "www.jrgermanier.ch",
                    "NOTES2": "info@jrgermanier.ch",
                    "NOTES3": "027.346.12.16",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "600",
                    "CODE": "8",
                    "DESCRIPTION": "Jean-Marie Pont",
                    "NOTES1": "www.jmpont.ch",
                    "NOTES2": "cave@jmpont.ch",
                    "NOTES3": "079.262.02.43",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "599",
                    "CODE": "4",
                    "DESCRIPTION": "Jean-Louis Mathieu",
                    "NOTES1": "www.mathieu-vins.ch",
                    "NOTES2": "jean-louis@mathieu-vins.ch",
                    "NOTES3": "027.458.27.63",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "598",
                    "CODE": "116",
                    "DESCRIPTION": "Imesch Vins SA",
                    "NOTES1": "www.imesch–vins.ch",
                    "NOTES2": "info@imesch–vins.ch",
                    "NOTES3": "027.455.10.65",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "597",
                    "CODE": "129",
                    "DESCRIPTION": "Henri Valloton",
                    "NOTES1": "www.valloton.ch",
                    "NOTES2": "info@valloton.ch",
                    "NOTES3": "027.746.28.89",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "596",
                    "CODE": "38",
                    "DESCRIPTION": "Cave des Rois",
                    "NOTES1": "www.cavedesrois.ch",
                    "NOTES2": "info@cavedesrois.ch",
                    "NOTES3": "021.944.41.28",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "595",
                    "CODE": "107",
                    "DESCRIPTION": "Gregor Kuonen",
                    "NOTES1": "www.gregor–kuonen.ch",
                    "NOTES2": "info@gregor–kuonen.ch",
                    "NOTES3": "027.451.21.21",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "594",
                    "CODE": "17",
                    "DESCRIPTION": "Gilbert Devayes",
                    "NOTES1": "www.cavedevayes.ch",
                    "NOTES2": "info@gilbertdevayes.ch",
                    "NOTES3": "027.306.25.96",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "593",
                    "CODE": "5",
                    "DESCRIPTION": "Germanus Kellerei",
                    "NOTES1": "www.germanus.ch",
                    "NOTES2": "kellerei@germanus.ch",
                    "NOTES3": "027.934.35.17",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "592",
                    "CODE": "149",
                    "DESCRIPTION": "Cave de l\'Orlaya SA",
                    "NOTES1": "www.orlaya.ch",
                    "NOTES2": "info@orlaya.ch",
                    "NOTES3": "027.746.28.10",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "591",
                    "CODE": "93",
                    "DESCRIPTION": "Cave Raymond",
                    "NOTES1": "www.caveraymond.ch",
                    "NOTES2": "vins@caveraymond.ch",
                    "NOTES3": "027.744.30.24",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "590",
                    "CODE": "77",
                    "DESCRIPTION": "Domaine Gérald Besse",
                    "NOTES1": "www.besse.ch",
                    "NOTES2": "gerald@besse.ch",
                    "NOTES3": "027.722.78.81",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "589",
                    "CODE": "146",
                    "DESCRIPTION": "Fernand Cina SA",
                    "NOTES1": "www.fernand–cina.ch",
                    "NOTES2": "caves@fernand–cina.ch",
                    "NOTES3": "027.455.09.08",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "588",
                    "CODE": "55",
                    "DESCRIPTION": "Erhard Mathier - Vins GmbH",
                    "NOTES1": "www.erhardmathiervins.com",
                    "NOTES2": "info@m–vins.ch",
                    "NOTES3": "027.455.15.51",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "587",
                    "CODE": "104",
                    "DESCRIPTION": "Dominique Passaquay",
                    "NOTES1": "www.vinspassaquay.ch",
                    "NOTES2": "info@vinspassaquay.ch",
                    "NOTES3": "024.471.18.01",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "586",
                    "CODE": "115",
                    "DESCRIPTION": "Domaines Rouvinez SA",
                    "NOTES1": "www.rouvinez.com",
                    "NOTES2": "info@rouvinez.com",
                    "NOTES3": "027.452.22.45",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "584",
                    "CODE": "78",
                    "DESCRIPTION": "Domaine La Rodeline",
                    "NOTES1": "www.rodeline.ch",
                    "NOTES2": "rodeline@mycable.ch",
                    "NOTES3": "027.746.17.54",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "583",
                    "CODE": "74",
                    "DESCRIPTION": "Domaine du Mont d Or SA Sion",
                    "NOTES1": "www.montdor–wine.ch",
                    "NOTES2": "info@montdor.ch",
                    "NOTES3": "027.346.20.32",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "582",
                    "CODE": "52",
                    "DESCRIPTION": "Domaine du Grand-Brûlé",
                    "NOTES1": "www.vs.ch/web/sca/domaine-du-grand-brule",
                    "NOTES2": "eddy.dorsaz@admin.vs.ch",
                    "NOTES3": "027.606.76.80",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "581",
                    "CODE": "32",
                    "DESCRIPTION": "Domaine des Muses",
                    "NOTES1": "www.domainedesmuses.ch",
                    "NOTES2": "info@domainedesmuses.ch",
                    "NOTES3": "027.455.73.09",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "580",
                    "CODE": "128",
                    "DESCRIPTION": "Domaine Cornulus",
                    "NOTES1": "www.cornulus.ch",
                    "NOTES2": "cornulus@bluewin.ch",
                    "NOTES3": "027.395.25.45",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "579",
                    "CODE": "60",
                    "DESCRIPTION": "Distillerie Morand Louis & Cie SA",
                    "NOTES1": "www.morand.ch",
                    "NOTES2": "info@morand.ch",
                    "NOTES3": "027.720.41.40",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "578",
                    "CODE": "14",
                    "DESCRIPTION": "Grand­Père Cornut",
                    "NOTES1": "www.grandperecornut.com",
                    "NOTES2": "yves@grandperecornut.com",
                    "NOTES3": "079.221.19.19",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "577",
                    "CODE": "97",
                    "DESCRIPTION": "Anne-Catherine et Denis Mercier",
                    "NOTES1": "www.mercier–vins.ch",
                    "NOTES2": "denis.mercier@netplus.ch",
                    "NOTES3": "027.455.47.10",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "576",
                    "CODE": "16",
                    "DESCRIPTION": "Defayes et Crettenand",
                    "NOTES1": "www.defayes.com",
                    "NOTES2": "vins@defayes.com",
                    "NOTES3": "027.306.28.07",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "575",
                    "CODE": "65",
                    "DESCRIPTION": "Daniel Magliocco et Fils SA",
                    "NOTES1": "www.maglioccovins.ch",
                    "NOTES2": "mikael@maglioccovins.ch",
                    "NOTES3": "079 445 88 88",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "574",
                    "CODE": "9",
                    "DESCRIPTION": "Clos de Géronde Sierre",
                    "NOTES1": "www.fredericzuffereyvins.ch",
                    "NOTES2": "zuffereyfredericvins@netplus.ch",
                    "NOTES3": "027.456.10.59",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "573",
                    "CODE": "6",
                    "DESCRIPTION": "Christophe Rey",
                    "NOTES1": "www.caverey.ch",
                    "NOTES2": "info@caverey.ch",
                    "NOTES3": "027.455.19.46",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "572",
                    "CODE": "126",
                    "DESCRIPTION": "Chevalier Bayard",
                    "NOTES1": "www.chevalier–bayard.ch",
                    "NOTES2": "cave@chevalier–bayard.ch",
                    "NOTES3": "027.473.24.81",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "571",
                    "CODE": "50",
                    "DESCRIPTION": "Château Ravire",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "027.455.01.54",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "569",
                    "CODE": "123",
                    "DESCRIPTION": "Charles Bonvin SA",
                    "NOTES1": "www.bonvin1858.ch",
                    "NOTES2": " info@celliers.ch",
                    "NOTES3": "027.205.65.19",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "568",
                    "CODE": "82",
                    "DESCRIPTION": "Chanton Weine Visp",
                    "NOTES1": "www.chanton.ch",
                    "NOTES2": "weine@chanton.ch",
                    "NOTES3": "027.946.21.53",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "567",
                    "CODE": "114",
                    "DESCRIPTION": "Caves Orsat SA - Martigny",
                    "NOTES1": "www.rouvinez.com",
                    "NOTES2": "info@cavesorsat.ch",
                    "NOTES3": "027.721.01.01",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "566",
                    "CODE": "87",
                    "DESCRIPTION": "Caves du Paradis",
                    "NOTES1": "www.cavesduparadis.ch",
                    "NOTES2": "roten@cavesduparadis.ch",
                    "NOTES3": "027.455.19.03",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "564",
                    "CODE": "105",
                    "DESCRIPTION": "Cave Saint-Pierre SA",
                    "NOTES1": "www.saintpierre.ch",
                    "NOTES2": "info@saintgeorges.ch",
                    "NOTES3": "027.455.11.50",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "563",
                    "CODE": "26",
                    "DESCRIPTION": "Cave Saint-Philippe",
                    "NOTES1": "www.cave–st–philippe.ch",
                    "NOTES2": "info@cave–st–philippe.ch",
                    "NOTES3": "027.455.72.36",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "561",
                    "CODE": "30",
                    "DESCRIPTION": "Cave Saint-Georges",
                    "NOTES1": "www.saintgeorges.ch",
                    "NOTES2": "info@saintgeorges.ch",
                    "NOTES3": "027.455.11.50",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "559",
                    "CODE": "13",
                    "DESCRIPTION": "Cave Philippe et Veronyc Mettaz",
                    "NOTES1": "www.mettaz.ch",
                    "NOTES2": "info@mettaz.ch",
                    "NOTES3": "027.746.38.16",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "558",
                    "CODE": "80",
                    "DESCRIPTION": "Cave Mabillard-Fuchs",
                    "NOTES1": "",
                    "NOTES2": "mabillard–fuchs@bluewin.ch",
                    "NOTES3": "027.455.34.76",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "556",
                    "CODE": "33",
                    "DESCRIPTION": "Cave Les Sentes",
                    "NOTES1": "www.heymozvins.ch",
                    "NOTES2": "serge@heymozvins.ch",
                    "NOTES3": "027.456.25.75",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "555",
                    "CODE": "79",
                    "DESCRIPTION": "Cave Les Ruinettes",
                    "NOTES1": "www.sergeroh.ch",
                    "NOTES2": "serge.roh@bluewin.ch",
                    "NOTES3": "027.346.13.63",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "554",
                    "CODE": "118",
                    "DESCRIPTION": "Cave Le Rhyton d Or",
                    "NOTES1": "www.rhytondor.ch",
                    "NOTES2": "info@rhytondor.com",
                    "NOTES3": "027.306.20.24",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "553",
                    "CODE": "18",
                    "DESCRIPTION": "Cave Le Bosset SA",
                    "NOTES1": "www.lebosset.ch",
                    "NOTES2": "cave@lebosset.ch",
                    "NOTES3": "027.306.18.80",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "552",
                    "CODE": "109",
                    "DESCRIPTION": "Domaine Angélus",
                    "NOTES1": "www.domaine-angelus.ch",
                    "NOTES2": "info@domaine-angelus.ch",
                    "NOTES3": "079.206.88.74",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "550",
                    "CODE": "89",
                    "DESCRIPTION": "Cave La Tornale",
                    "NOTES1": "www.latornale.ch",
                    "NOTES2": "jd.favre@latornale.ch",
                    "NOTES3": "027.306.22.65",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "549",
                    "CODE": "122",
                    "DESCRIPTION": "Cave La Tine",
                    "NOTES1": "www.cavelatine.ch",
                    "NOTES2": "info@cavelatine.ch",
                    "NOTES3": "027.346.47.47",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "548",
                    "CODE": "12",
                    "DESCRIPTION": "Cave La Romaine",
                    "NOTES1": "www.cavelaromaine.ch",
                    "NOTES2": "info@cavelaromaine.ch",
                    "NOTES3": "027.458.46.22",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "547",
                    "CODE": "58",
                    "DESCRIPTION": "Cave La Madeleine",
                    "NOTES1": "www.fontannaz.ch",
                    "NOTES2": "info@fontannaz.ch",
                    "NOTES3": "027.346.46.54",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "546",
                    "CODE": "91",
                    "DESCRIPTION": "Cave La Liaudisaz",
                    "NOTES1": "www.chappaz.ch",
                    "NOTES2": "marie–therese@chappaz.ch",
                    "NOTES3": "027.746.35.37",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "545",
                    "CODE": "49",
                    "DESCRIPTION": "Cave La Fournaise",
                    "NOTES1": "www.cavefournaise.ch",
                    "NOTES2": "info@cavefournaise.ch",
                    "NOTES3": "079.204.39.87",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "544",
                    "CODE": "62",
                    "DESCRIPTION": "Cave Fin Bec SA",
                    "NOTES1": "www.finbec.ch",
                    "NOTES2": "info@finbec.ch",
                    "NOTES3": "027.346.20.17",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "543",
                    "CODE": "39",
                    "DESCRIPTION": "Cave du Vieux-Moulin",
                    "NOTES1": "www.papilloud.com",
                    "NOTES2": "papilloud@bluewin.ch",
                    "NOTES3": "027.346.43.22",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "542",
                    "CODE": "76",
                    "DESCRIPTION": "Cave du Rhodan",
                    "NOTES1": "www.rhodan.ch",
                    "NOTES2": "mounir@rhodan.ch",
                    "NOTES3": "027.455.04.07",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "540",
                    "CODE": "7",
                    "DESCRIPTION": "Cave du Crêtacombe",
                    "NOTES1": "www.cretacombe.ch",
                    "NOTES2": "cave@cretacombe.ch",
                    "NOTES3": "027.306.42.19",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "539",
                    "CODE": "85",
                    "DESCRIPTION": "Cave du Chavalard",
                    "NOTES1": "www.caveduchavalard.ch",
                    "NOTES2": "info@caveduchavalard.ch",
                    "NOTES3": "027.746.23.55",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "538",
                    "CODE": "121",
                    "DESCRIPTION": "Cave des Tilleuls SA",
                    "NOTES1": "www.fabiennecottagnoud.ch",
                    "NOTES2": "info@fabiennecottagnoud.ch",
                    "NOTES3": "079.409.25.44",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "537",
                    "CODE": "72",
                    "DESCRIPTION": "Cave des Remparts",
                    "NOTES1": "www.cavedesremparts.ch",
                    "NOTES2": "cave.des.remparts@saillon.ch",
                    "NOTES3": "079.401.48.37",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "536",
                    "CODE": "54",
                    "DESCRIPTION": "Cave des Places",
                    "NOTES1": "www.hugvins.ch",
                    "NOTES2": "info@hugvins.ch",
                    "NOTES3": "027.398.31.43",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "535",
                    "CODE": "61",
                    "DESCRIPTION": "Cave des Champs",
                    "NOTES1": "www.claudy–clavien.ch",
                    "NOTES2": "vins@claudy–clavien.ch",
                    "NOTES3": "027.455.24.23",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "534",
                    "CODE": "88",
                    "DESCRIPTION": "Cave des Bernunes",
                    "NOTES1": "www.cavebernunes.ch",
                    "NOTES2": "cave.bernunes@bluewin.ch",
                    "NOTES3": "027.456.51.41",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "532",
                    "CODE": "108",
                    "DESCRIPTION": "Cave de Montorge",
                    "NOTES1": "www.favre–vins.ch",
                    "NOTES2": "info@favre–vins.ch",
                    "NOTES3": "027.327.50.50",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "531",
                    "CODE": "51",
                    "DESCRIPTION": "Cave de Chateauneuf",
                    "NOTES1": "www.vs.ch/web/sca/espace-merlot",
                    "NOTES2": "espace–merlot@admin.vs.ch",
                    "NOTES3": "027.606.76.80",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "530",
                    "CODE": "99",
                    "DESCRIPTION": "Cave d Anchettes",
                    "NOTES1": "",
                    "NOTES2": "anchettes@bluewin.ch",
                    "NOTES3": "027.455.14.57",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "529",
                    "CODE": "29",
                    "DESCRIPTION": "Cave Colline de Daval",
                    "NOTES1": "www.collinededaval.ch",
                    "NOTES2": "castel@collinededaval.ch",
                    "NOTES3": "027.458.45.15",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "528",
                    "CODE": "20",
                    "DESCRIPTION": "Cave Caprice du Temps",
                    "NOTES1": "www.capricedutemps.com",
                    "NOTES2": "clavien@capricedutemps.com",
                    "NOTES3": "027.455.76.40",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "527",
                    "CODE": "117",
                    "DESCRIPTION": "Cave Ardévaz SA",
                    "NOTES1": "www.boven.ch",
                    "NOTES2": "info@boven.ch",
                    "NOTES3": "027.306.28.36",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "526",
                    "CODE": "132",
                    "DESCRIPTION": "Calvalais",
                    "NOTES1": "www.calvalais.ch",
                    "NOTES2": "soliozmaurice@gmail.com",
                    "NOTES3": "079.219.25.64",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "525",
                    "CODE": "119",
                    "DESCRIPTION": "Bernard Dupont",
                    "NOTES1": "www.abricotine–dupont.ch",
                    "NOTES2": "abricotine–dupont@saxon.ch",
                    "NOTES3": "027.744.23.48",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "524",
                    "CODE": "95",
                    "DESCRIPTION": "Benoit Dorsaz SA",
                    "NOTES1": "www.benoit–dorsaz.ch",
                    "NOTES2": "info@benoit–dorsaz.ch",
                    "NOTES3": "027.746.11.25",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "523",
                    "CODE": "35",
                    "DESCRIPTION": "Antoine & Christophe Bétrisey",
                    "NOTES1": "www.betrisey–vins.ch",
                    "NOTES2": "ventes@betrisey–vins.ch",
                    "NOTES3": "027.203.11.26",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "522",
                    "CODE": "83",
                    "DESCRIPTION": "Albert Mathier & Söhne SA",
                    "NOTES1": "www.mathier.ch",
                    "NOTES2": "info@mathier.ch",
                    "NOTES3": "027.455.14.19",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "521",
                    "CODE": "90",
                    "DESCRIPTION": "Albert Biollaz SA",
                    "NOTES1": "www.biollaz–vins.ch",
                    "NOTES2": "info@biollaz–vins.ch",
                    "NOTES3": "027.306.28.86",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "520",
                    "CODE": "112",
                    "DESCRIPTION": "Abricool SA",
                    "NOTES1": "www.abricool.ch",
                    "NOTES2": "abricool@bluewin.ch",
                    "NOTES3": "027.744.24.56",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "31",
                    "CODE": "1030",
                    "DESCRIPTION": "Tranches au Fromage",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "30",
                    "CODE": "1029",
                    "DESCRIPTION": "raclettes",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "29",
                    "CODE": "1028",
                    "DESCRIPTION": "Fondues",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "28",
                    "CODE": "1027",
                    "DESCRIPTION": "Entrée",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "26",
                    "CODE": "1025",
                    "DESCRIPTION": "Desserts ",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "25",
                    "CODE": "1024",
                    "DESCRIPTION": "Petit plus",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "24",
                    "CODE": "1023",
                    "DESCRIPTION": "accompagement",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "23",
                    "CODE": "1022",
                    "DESCRIPTION": "Plat principal",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "22",
                    "CODE": "1021",
                    "DESCRIPTION": "Menus groupes",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "21",
                    "CODE": "1020",
                    "DESCRIPTION": "Matériel",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "20",
                    "CODE": "1019",
                    "DESCRIPTION": "Salles",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "19",
                    "CODE": "1018",
                    "DESCRIPTION": "Livres et DVD",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "18",
                    "CODE": "1017",
                    "DESCRIPTION": "Emballages",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "17",
                    "CODE": "1016",
                    "DESCRIPTION": "Coffrets cadeau ",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "16",
                    "CODE": "1015",
                    "DESCRIPTION": "Fromages",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "15",
                    "CODE": "1014",
                    "DESCRIPTION": "Produits sec",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "14",
                    "CODE": "1013",
                    "DESCRIPTION": "Salaisons",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "13",
                    "CODE": "1012",
                    "DESCRIPTION": "Plats froids",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "12",
                    "CODE": "1011",
                    "DESCRIPTION": "Thé - Café",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "11",
                    "CODE": "1010",
                    "DESCRIPTION": "Jus de Fruits",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "10",
                    "CODE": "1009",
                    "DESCRIPTION": "Minérales",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "9",
                    "CODE": "1008",
                    "DESCRIPTION": "Locations",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "8",
                    "CODE": "1007",
                    "DESCRIPTION": "Forfaits séminaires",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "7",
                    "CODE": "1006",
                    "DESCRIPTION": "Forfaits vins",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "6",
                    "CODE": "1005",
                    "DESCRIPTION": "Divers ",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "5",
                    "CODE": "1004",
                    "DESCRIPTION": "Produits boutique",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "4",
                    "CODE": "1003",
                    "DESCRIPTION": "nourriture",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "3",
                    "CODE": "1002",
                    "DESCRIPTION": "Cigares",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "2",
                    "CODE": "1001",
                    "DESCRIPTION": "Bières ",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  },
                  {
                    "ID": "1",
                    "CODE": "1000",
                    "DESCRIPTION": "Boissons sans alcool",
                    "NOTES1": "",
                    "NOTES2": "",
                    "NOTES3": "",
                    "OWNER_GROUP_ID": ""
                  }
                ]
              }'
        );

        return $data;
    }
}
