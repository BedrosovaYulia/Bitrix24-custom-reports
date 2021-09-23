<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main;
use \Bitrix\Main\Localization;

try{
    $application = Main\Application::getInstance();
    $request = $application->getContext()->getRequest();

    $parameters = [
        'PATH_TO_USER_PROFILE' => '/company/personal/user/#USER_ID#/',
        'PATH_TO_SUPPORT_TICKET' => '/bitrix/admin/ticket_edit.php?ID=#TICKET_ID#',
    ];

    $elementsOnPage = 20;

    if(!$USER->IsAuthorized()){
        throw new \Exception('Not Authorized. [030520016.1443.1]');
    }

    if(!Main\Loader::includeModule('support')){
        throw new \Exception('"support" module is not installed. [03052016.1444.1]');
    }

    if(!Main\Loader::includeModule('intreface.crmhelpdesk')){
        throw new \Exception('"intreface.crmhelpdesk" module is not installed. [03052016.1849.1]');
    }

    $company_id = (int)$request->getQuery('company_id');
    if($company_id <= 0){
        throw new \Exception('Company ID is incorrect. [03052016.1447.1]');
    }

    $result = [
        'grid_id' => 'crm_helpdesk_grid',
        'headers' => [
            [
                'id' => 'ID',
                'name' => Localization\Loc::getMessage('column_ID'),
                'sort' => 'id',
                'default' => true,
                'editable' => false,
                'type' => 'number',
                'class' => 'minimal'
            ],
            [
                'id' => 'DATE_CREATE',
                'name' => Localization\Loc::getMessage('column_DATE_CREATE'),
                'sort' => 'date_create',
                'default' => true,
                'editable' => false,
                'type' => 'date',
                'class' => 'minimal'
            ],
            [
                'id' => 'STATUS',
                'name' => Localization\Loc::getMessage('column_STATUS'),
                'sort' => 'lamp',
                'default' => true,
                'editable' => false,
                'type' => 'text',
                'class' => 'minimal'
            ],
            [
                'id' => 'TITLE',
                'name' => Localization\Loc::getMessage('column_TITLE'),
                'sort' => 'title',
                'default' => true,
                'editable' => false,
                'type' => 'text',
                'class' => 'minimal'
            ],
            [
                'id' => 'DEADLINE',
                'name' => Localization\Loc::getMessage('column_DEADLINE'),
                'sort' => 'deadline',
                'default' => true,
                'editable' => false,
                'type' => 'date',
                'class' => 'minimal'
            ],
            [
                'id' => 'RESPONSIBLE',
                'name' => Localization\Loc::getMessage('column_RESPONSIBLE'),
                'sort' => 'responsible',
                'default' => true,
                'editable' => false,
                'type' => 'text',
                'class' => 'minimal'
            ],
        ],
        'data_raw' => [],
        'data' => [],
    ];

    $grid = new \CGridOptions($result["GRID_ID"]);
    $nav = $grid->GetNavParams(["nPageSize" => $elementsOnPage]);
    $sort = $grid->GetSorting(["sort" => ["created_date_short" => "desc"], "vars" => ["by" => "by", "order" => "order"]]);

    // Gather and prepare data
    $source = \CTicket::GetList($by = 'ID', $order = 'DESC', ['=UF_CRM_COMPANY' => $company_id], $isFiltered, 'N');
    while($record = $source->Fetch()){
        $created = new Main\Type\Date($record['DATE_CREATE']);
        $deadline = '';
        if(Main\Type\Date::isCorrect($record['SUPPORT_DEADLINE'])){
            $deadline = new Main\Type\Date($record['SUPPORT_DEADLINE']);
        }

        $result['data_raw'][$record['ID']] = [
            'ID' => $record['ID'],
            'DATE_CREATE' => $created,
            'LAMP' => $record['LAMP'],
            'TITLE' => $record['TITLE'],
            'DEADLINE' => $deadline,
            'RESPONSIBLE_LOGIN' => $record["RESPONSIBLE_LOGIN"],
            'RESPONSIBLE_NAME' => $record['RESPONSIBLE_NAME'],
            'RESPONSIBLE_USER_ID' => $record['RESPONSIBLE_USER_ID'],
        ];
    }

    // Resort rules
    $rule = each($sort['sort']);
    $rule = [
        'key' => \ToUpper($rule['key']),
        'value' => \ToUpper($rule['value'])
    ];

    if(count(array_filter($rule)) == 2){
        // Resort
        usort($result['data_raw'], function($i1, $i2) use($rule){
            // Exception
            if($rule['key'] == 'DEADLINE'){
                if(array_key_exists($rule['key'], $i1) && $i1[$rule['key']] == ''){
                    return 1; // move to end of list
                }
                if(array_key_exists($rule['key'], $i2) && $i2[$rule['key']] == ''){
                    return -1; // move to end of list
                }
            }
            if(!array_key_exists($rule['key'], $i1) || !array_key_exists($rule['key'], $i2) || $i1[$rule['key']] == $i2[$rule['key']]){
                return 0;
            }

            switch($rule['value']){
                case 'ASC':
                    return ($i1[$rule['key']] > $i2[$rule['key']])?1:-1;
                    break;

                case 'DESC':
                    return ($i1[$rule['key']] > $i2[$rule['key']])?-1:1;
                    break;

                default:
                    return 0;
                    break;
            }
        });
    }

    // Re-Build
    $result['nav'] = new \CDBResult();
    $result['nav']->InitFromArray($result['data_raw']);
    $result['nav']->bShowAll = false;
    $result['nav']->NavStart($elementsOnPage);

    while($record = $result['nav']->GetNext()){
        $actions = [
            [
                "ICONCLASS" => "view",
                "TEXT" => Localization\Loc::getMessage('btn_VIEW'),
                "ONCLICK" => "intreface_crmhelpdesk.dialog.view.show(" . $record['ID'] . ");"
            ],
        ];

        $custom = [
            'ID' => '<a data-ticket-id="' . $record['ID'] . '" href="' . str_replace(['#TICKET_ID#'], [$record['ID']], $parameters['PATH_TO_SUPPORT_TICKET']) . '" target="_blank">' . $record['ID'] . '</a>',
            'STATUS' => '<div class="support-status-bar status-' . $record['LAMP'] . '"></div>',
            'RESPONSIBLE' => $record['RESPONSIBLE_NAME']
                ?
                :$record['RESPONSIBLE_LOGIN']
            ,
        ];

        if(strlen($custom['RESPONSIBLE']) > 0){
            $custom['RESPONSIBLE'] = '<a href="' . str_replace(['#USER_ID#'], [$record['RESPONSIBLE_USER_ID']], $parameters['PATH_TO_USER_PROFILE']) . '" target="_blank"><span class="crm-detail-info-resp-name">' . $custom['RESPONSIBLE'] . '</span></a>';
        }

        $custom['STATUS'] .= '<div class="support-status-description">' . Localization\Loc::getMessage('column_STATUS_description#' . \ToLower($record['LAMP'])) . '</div>';

        $result["data"][] = [
            "id" => $record['ID'],
            "data" => $record,
            "actions" => $actions,
            "editable" => false,
            "columns" => $custom,
        ];
    }

    // Display head block
    $APPLICATION->ShowAjaxHead();

    // Include toolbar component
    $APPLICATION->IncludeComponent(
        'bitrix:crm.interface.toolbar',
        '',
        [
            'TOOLBAR_ID' => strtolower($result['grid_id']) . '_toolbar',
            'BUTTONS' => [
                [
                    'TEXT' => Localization\Loc::getMessage('btn_ADD_TICKET'),
                    'TITLE' => Localization\Loc::getMessage('btn_ADD_TICKET'),
                    'LINK' => '',
                    'ICON' => 'btn-new',
                    'ONCLICK' => "intreface_crmhelpdesk.dialog.create.show()"
                ]
            ]
        ],
        null,
        ["HIDE_ICONS" => "Y"]
    );

    // Include grid compoment
    $APPLICATION->IncludeComponent(
        "bitrix:main.interface.grid",
        "",
        [
            "GRID_ID" => $result['grid_id'],
            "HEADERS" => $result['headers'],
            "ROWS" => $result['data'],
            //"ACTIONS" => $actions,
            "NAV_OBJECT" => $result['nav'],
            "ACTION_ALL_ROWS" => false,
            "EDITABLE" => false,
            "SORT" => $sort['sort'],
            "FILTER" => $arResult["FILTER"],
            "FOOTER" => [
                ["title" => 'Total', "value" => $result['nav']->SelectedRowsCount()]
            ],
            "AJAX_MODE" => "Y",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_HISTORY" => 'N',
        ],
        null,
        ["HIDE_ICONS" => "Y"]
    );

    $crmContactEntity = new \CCrmContact(false);
    $crmMultiField = new \CCrmFieldMulti();
    // Append all contacts related to company
    $contactList = [];
    $source = $crmContactEntity->GetList(['FULL_NAME' => 'ASC'], ['=COMPANY_ID' => $company_id]);
    while($record = $source->Fetch()){
        $email = $crmMultiField->GetList([], ['=ELEMENT_ID' => (int)$record['ID'], '=ENTITY_ID' => 'CONTACT', 'TYPE_ID' => \CCrmFieldMulti::EMAIL, '=VALUE_TYPE' => 'WORK'])->Fetch()['VALUE'];
        if(\check_email($email)){
            $contactList[$email] = $record['FULL_NAME'];
        }
    }

    $asset = Main\Page\Asset::getInstance();
    $asset->addString('
    <script type="text/javascript">
        $(function(){
            intreface_crmhelpdesk.data.contacts = ' . Main\Web\Json::encode($contactList) . ';
        });
    </script>
    ');
}
catch (\Exception $e){
    echo $e->getMessage();
}