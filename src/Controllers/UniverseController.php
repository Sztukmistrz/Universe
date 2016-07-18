<?php

namespace Sztukmistrz\Universe\Controllers;

use App\Http\Controllers\Controller;


class UniverseController extends Controller
{

    private $baseRouteName = 'Universe.page.';

    private $baseViewName = 'layouter::backend.';

    private $realmName = 'universe';

    /**
     * Funkcja startowa dla królestwa pagina
     * pokazuje menu królestwa dla zasobów
     * pokazuje ststystyki
     * pokazuje opis królestwa, instrukcje.
     * @return [type] [description]
     */
    public function start(BackendRealm $backendRealm)
    {
        # - - - - - - - - - - - - - - - - - - - - - - - -
        $menusRealm    = false;//$backendRealm->getRealmMenu($this->realmName);
        //$developerMenu = $backendRealm->getDeveloperMainMenu();
        //$adminMenu     = $backendRealm->getAdminMainMenu();
        # - - - - - - - - - - - - - - - - - - - - - - - -

        # - - - - - - - - - - - - - - - - - - - - - - - -
        $ststistics    = 'stats universe';
        $descriptions  = 'descriptions of universe';
        # - - - - - - - - - - - - - - - - - - - - - - - -

        # - - - - - - - - - - - - - - - - - - - - - - - -
        return view($this->baseViewName . __FUNCTION__,
            compact(
                'menusRealm' 
                ///'developerMenu', 
                //'adminMenu',
               // 'ststistics', 'descriptions'
            )
        );
        # - - - - - - - - - - - - - - - - - - - - - - - -
    }



}
