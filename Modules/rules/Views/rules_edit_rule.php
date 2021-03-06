<?php
/* * * Global variables  ** */
global $path, $mysqli, $redis, $session, $route;

/* * * Includes  ** */
include_once "Modules/rules/rules_model.php";
$rules = new Rules($mysqli, $redis);
// ToDo - check if Register Module is installed, if it is: Attributes will be added as Reporters. This is done to make the Rules module not dependant on the Register Module
include_once "Modules/register/register_model.php";
$register = new Register($mysqli);

/* * *  We set the header text according to the mode  ** */
if ($args['mode'] == 'add')
    $header = 'New Rule';
else
    $header = 'Edit Rule';

/* * * Arrays containing the attributes and feeds (sorted by node or tag) to be used in the visual programmer  ** */
$array_of_attributes_by_node = $register->getAttributesByNode($session['userid']);
//$array_of_feeds_by_node = $rules->get_user_feeds_by_node($session['userid']); // array like: Array ( [0] => Array ( [id] => 6 [name] => Power [userid] => 1 [tag] => [time] => 1430748375 [value] => 100 [datatype] => 1 [public] => 0 [size] => [engine] => 5 ) [1] => Array ( [id] => 7 [name] => Poadasdawer [userid] => 1 [tag] => [time] => [value] => [datatype] => 1 [public] => 0 [size] => [engine] => 5 ) [2] => Array ( [id] => 8 [name] => Poaeeedasdawer [userid] => 1 [tag] => [time] => [value] => [datatype] => 1 [public] => 0 [size] => [engine] => 5 ));
$array_of_feeds_by_tag = $rules->get_user_feeds_by_tag($session['userid']); // array like: Array ( [0] => Array ( [id] => 6 [name] => Power [userid] => 1 [tag] => [time] => 1430748375 [value] => 100 [datatype] => 1 [public] => 0 [size] => [engine] => 5 ) [1] => Array ( [id] => 7 [name] => Poadasdawer [userid] => 1 [tag] => [time] => [value] => [datatype] => 1 [public] => 0 [size] => [engine] => 5 ) [2] => Array ( [id] => 8 [name] => Poaeeedasdawer [userid] => 1 [tag] => [time] => [value] => [datatype] => 1 [public] => 0 [size] => [engine] => 5 ));


/* * * Are we in developer mode, $rules_developer_mode should be declared in settings.php, the default value is false  ** */
include "settings.php";
if (!isset($rules_developer_mode))
    $isDev = false;
else
    $isDev = $rules_developer_mode;

/* echo '<pre>';
  print_r($array_of_attributes_by_node);
  echo '</pre>'; */
?>
<!--<script type="text/javascript" src="<?php //echo $path;                              ?>Lib/angularjs/angular.min.js"></script>-->
<script type="text/javascript" src="<?php echo $path; ?>Lib/angularjs/angular.js"></script>

<!-- Visual programmer  -->
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/morphic.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/emonCMS_RulesMorphic.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/emonCMS_RulesIDE_gui.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/widgets.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/objects.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/emonCMS_RulesObjects.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/blocks.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/emonCMS_RulesBlocks.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/threads.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/byob.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/emonCMS_RulesByob.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/xml.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/rules/scripts/store.js"></script>


<script>
    var moduleViewApp = angular.module('moduleViewApp', []);

    moduleViewApp.controller('moduleViewAppCtrl', function ($scope, $window, $http) {

        /*  Objects in the scope  */
<?php
if (isset($args['rule'])) {
    $rule = $args['rule'];
    ?>
            $scope.rule_attributes = {'ruleid': '<?php echo $rule['ruleid'] ?>',
                'name': '<?php echo $rule['name'] ?>',
                'description': '<?php echo $rule['description'] ?>',
                'run_on': '<?php echo $rule['run_on'] ?>',
                'expiry_date': '<?php echo $rule['expiry_date'] ?>',
                'frequency': Number('<?php echo $rule['frequency'] ?>'),
                'enabled':<?php
    if ($rule['enabled'] == 1)
        echo 'true';
    else
        echo 'false';
    ?>
                // we don't include the "blocks" here because they are not used in the angularjs scope, they are used in the Morphic world
            };
            //console.log($scope.rule_attributes);
            $scope.rule_saved = <?php echo isset($args['rule_saved']) ? json_encode($args['rule_saved']) : "null" ?>;
<?php } else {
    ?>
            $scope.rule_attributes = {'ruleid': '', 'name': '', 'description': '', 'run_on': '', 'expiry_date': '', 'frequency': '', 'blocks': '', 'enabled': false};
            $scope.rule_saved = null;
<?php } ?>
        /*  End Objects in the scope  */

        /*  Functions in the scope  */
        $scope.save_apply = function (mode) {
            var href = 'rules/save-rule?name=' + $scope.rule_attributes.name
                    + "&description=" + $scope.rule_attributes.description
                    + "&run_on=" + $scope.rule_attributes.run_on
                    + "&expiry_date=" + $scope.rule_attributes.expiry_date
                    + "&frequency=" + $scope.rule_attributes.frequency
                    + "&blocks=" + rulesIDE.generateXML()
                    + "&ruleid=" + $scope.rule_attributes.ruleid
                    + "&enabled=" + $scope.rule_attributes.enabled
                    + "&mode=" + mode;
            $window.location.href = "<?php echo $path; ?>" + href;
        };
        $scope.test_rule = function () {
            /*var req = {
             method: 'POST',
             url: 'rules/getPhpCode.json',
             headers: {
             'Content-Type': undefined
             },
             data: {test: 'test'}
             }
             
             $http(req).then(function (data) {
             $scope.xml_blocks = rulesIDE.generateXML();
             $scope.php_code = data.php_code;
             $scope.syntax_check = data.syntax_check;
             $scope.show_php_code = true;
             console.log(data);
             });
             */

            /*$http.post("rules/getPhpCode.json", {'blocks': "'hola'"},{}).then(function (data) {
             $scope.xml_blocks = rulesIDE.generateXML();
             $scope.php_code = data.php_code;
             $scope.syntax_check = data.syntax_check;
             $scope.show_php_code = true;
             console.log(data);
             });
             */
            $http.get("rules/getAndCheckPhpCode.json?id=<?php echo $rule['ruleid'] ?>&blocks=" + rulesIDE.generateXML()).then(function (result) {
                $scope.xml_blocks = rulesIDE.generateXML();
                $scope.php_code = result.data.php_code;
                $scope.syntax_check = result.data.syntax_check;
                $scope.eval_result = result.data.eval_result;
                $scope.show_php_code = true;
                console.log(result);
            });
        };
        /*  End Functions in the scope  */

    })
</script>

<script type="text/javascript">
    var world;
    window.onload = function () {
        // Create World
        world = new WorldMorph(document.getElementById('world'));
        world.worldCanvas.focus();
        world.isDevMode = Boolean(<?php echo $isDev; ?>);
        world.setWidth(1170);
        world.setHeight(450);

        // Create IDE and add it to the world - Everything related to the IDE is in "emonCMS_RulesIDE_gui.js" 
        var array_of_feeds_by_tag = <?php echo json_encode($array_of_feeds_by_tag) ?>;
        var array_of_attributes_by_node = <?php echo json_encode($array_of_attributes_by_node) ?>;
<?php if (isset($rule)) {
    ?>
            var blocks = '<?php echo str_replace('</script>', "' + '</scr' + 'ipt>' + '", $rule['blocks']) ?>' // This replacement is to avoid echoing "</scrpt>" which would close the javascript scrpt
<?php } else { ?>
            var blocks = null;
<?php } ?>
        rulesIDE = new emonCMS_RulesIDE_Morph(world.width(), world.height(), array_of_feeds_by_tag, array_of_attributes_by_node, blocks);
        world.add(rulesIDE);
        setInterval(loop, 1);
    };
    function loop() {
        world.doOneCycle();
    }
</script>





<link href="<?php echo $path; ?>Modules/rules/Views/rules.css" rel="stylesheet">

<base href="<?php echo $path; ?>">
<br>
<div ng-app="moduleViewApp" ng-controller="moduleViewAppCtrl" id="edit-rule">
    <div id="apihelphead">
        <div style="float:right;">
            <span class="like-link" ng-click="save_apply('save')"><?php echo _('Save and close') ?></span>
            <span class="like-link" ng-click="save_apply('apply')"><?php echo _('Apply') ?></span>
            <span class="like-link" ng-click="save_apply('apply_and_test')"><?php echo _('Apply and Test') ?></span>
            <a href="rules/list"><?php echo _('Cancel') ?></a>
        </div>
    </div>
    <div class="container">
        <div id="localheading"><h2><?php echo _($header); ?></h2></div>
        <div id="rule_saved" ng-if="rule_saved !== null"><p class="bg-success"><?php echo _('Rule saved: ') ?> {{rule_saved}}</p></div>
        <div class="container">
            <table class="table">
                <tr><td><?php echo _('Name') ?>: </td><td><input type="text" ng-model="rule_attributes.name"/></td></tr>
                <tr><td><?php echo _('Description') ?>: </td><td><input type="text" ng-model="rule_attributes.description"/></td></tr>
                <tr><td><?php echo _('Run on') ?>: </td><td><input type="datetime" ng-model="rule_attributes.run_on"/></td></tr>
                <tr><td><?php echo _('Expiry date') ?>: </td><td><input type="datetime" ng-model="rule_attributes.expiry_date"/><span>&nbsp;&nbsp;<?php echo _('YYYY-MM-DD - 0 for no expiry date') ?></span></td></tr>
                <tr><td><?php echo _('Frequency') ?>: </td><td><input type="number" ng-model="rule_attributes.frequency"/><span>&nbsp;&nbsp;<?php echo _('seconds (if \'0\' the rule will only be run once)') ?></span></td></tr>
                <tr><td><?php echo _('Enabled') ?>: </td><td><input type="checkbox" ng-model="rule_attributes.enabled" /></span></td></tr>
                <!-- <tr id="blocks-programmer"><td><?php //echo _('Blocks')                                       ?>: </td><td><textarea ng-model="rule_attributes.blocks"/></td></tr>-->
            </table>
            <h3><?php echo _('Script') ?></h3>
            <div id="blocks-programmer">
                <canvas id="world" tabindex="1" style="position: absolute"/>
            </div>
            <h3><?php echo _('Developer console') ?></h3>
            <div id="developer-console">
                <button ng-click="test_rule()"><?php echo _('Test rule') ?></button>

                <p>ToDo if there is a syntax error in the php code the ajax crashes, i am not sure what the problem is but it seems that that the error when runing eval makes the server to send a 500 internal error message.</p>
                <div ng-if="show_php_code">
                    <h4><?php echo _('XML blocks') ?></h4>
                    <pre>{{xml_blocks}}</pre>
                    <h4><?php echo _('Php code') ?></h4>
                    <p><?php echo _('Below is the php code generated for your rule') ?></p>
                    <pre>{{php_code}}</pre>
                    <h4><?php echo _('Sintax check') ?></h4>
                    <p><?php echo _('This is the message we get when we run "php -l" with the generated code') ?></p>
                    <pre>{{syntax_check}}</pre>
                    <p ng-if="syntax_check !== 'No syntax errors detected in -'">
                        <?php echo _('Because there are syntax errors, we have evaluated the code and this is the output:') ?>
                    </p>
                    <pre  ng-if="syntax_check !== 'No syntax errors detected in -'">{{eval_result}}</pre>

                    <h4><?php echo _('Run the rule') ?></h4>
                    <p><?php echo _('We run the rule for a minute') ?>  -  ToDo: run the rule (stage 1), keep checking acks</p>
                </div>
            </div>
        </div>
    </div>
</div>






