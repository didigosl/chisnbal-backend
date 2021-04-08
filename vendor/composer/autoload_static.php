<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb62cb559aeb32b7fdd59aa3e707ec665
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        'a0edc8309cc5e1d60e3047b5df6b7052' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
        '5255c38a0faeba867671b61dfda6d864' => __DIR__ . '/..' . '/paragonie/random_compat/lib/random.php',
        'ddc0a4d7e61c0286f0f8593b1903e894' => __DIR__ . '/..' . '/clue/stream-filter/src/functions.php',
        '023d27dca8066ef29e6739335ea73bad' => __DIR__ . '/..' . '/symfony/polyfill-php70/bootstrap.php',
        '8cff32064859f4559445b89279f3199c' => __DIR__ . '/..' . '/php-http/message/src/filters.php',
        'c964ee0ededf28c96ebd9db5099ef910' => __DIR__ . '/..' . '/guzzlehttp/promises/src/functions_include.php',
        '37a3dc5111fe8f707ab4c132ef1dbc62' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions_include.php',
        '667aeda72477189d0494fecd327c3641' => __DIR__ . '/..' . '/symfony/var-dumper/Resources/functions/dump.php',
        'e7223560d890eab89cda23685e711e2c' => __DIR__ . '/..' . '/psy/psysh/src/Psy/functions.php',
        '9e090711773bfc38738f5dbaee5a7f14' => __DIR__ . '/..' . '/overtrue/wechat/src/Payment/helpers.php',
    );

    public static $prefixLengthsPsr4 = array (
        'k' => 
        array (
            'kotchuprik\\short_id\\' => 20,
        ),
        'X' => 
        array (
            'XdgBaseDir\\' => 11,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Php70\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Component\\VarDumper\\' => 28,
            'Symfony\\Component\\OptionsResolver\\' => 34,
            'Symfony\\Component\\HttpFoundation\\' => 33,
            'Symfony\\Component\\Debug\\' => 24,
            'Symfony\\Component\\Console\\' => 26,
            'Symfony\\Bridge\\PsrHttpMessage\\' => 30,
            'Stripe\\' => 7,
            'SendinBlue\\Client\\' => 18,
        ),
        'Q' => 
        array (
            'Qcloud\\Sms\\' => 11,
            'QL\\' => 3,
        ),
        'P' => 
        array (
            'Psy\\' => 4,
            'Psr\\Log\\' => 8,
            'Psr\\Http\\Message\\' => 17,
            'Psr\\Container\\' => 14,
            'PhpParser\\' => 10,
            'Phalcon\\' => 8,
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'O' => 
        array (
            'Overtrue\\Socialite\\' => 19,
            'Omnipay\\Stripe\\' => 15,
            'Omnipay\\PayPal\\' => 15,
            'Omnipay\\Common\\' => 15,
            'OSS\\' => 4,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
            'Money\\' => 6,
        ),
        'J' => 
        array (
            'JPush\\' => 6,
        ),
        'H' => 
        array (
            'Http\\Promise\\' => 13,
            'Http\\Message\\' => 13,
            'Http\\Discovery\\' => 15,
            'Http\\Client\\' => 12,
            'Http\\Adapter\\Guzzle6\\' => 21,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Psr7\\' => 16,
            'GuzzleHttp\\Promise\\' => 19,
            'GuzzleHttp\\' => 11,
        ),
        'E' => 
        array (
            'Endroid\\QrCode\\' => 15,
            'EasyWeChat\\' => 11,
        ),
        'C' => 
        array (
            'Curl\\' => 5,
            'Clue\\StreamFilter\\' => 18,
        ),
        'B' => 
        array (
            'Buuum\\' => 6,
            'Braintree\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'kotchuprik\\short_id\\' => 
        array (
            0 => __DIR__ . '/..' . '/kotchuprik/php-short-id',
        ),
        'XdgBaseDir\\' => 
        array (
            0 => __DIR__ . '/..' . '/dnoegel/php-xdg-base-dir/src',
        ),
        'Symfony\\Polyfill\\Php70\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php70',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Component\\VarDumper\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/var-dumper',
        ),
        'Symfony\\Component\\OptionsResolver\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/options-resolver',
        ),
        'Symfony\\Component\\HttpFoundation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/http-foundation',
        ),
        'Symfony\\Component\\Debug\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/debug',
        ),
        'Symfony\\Component\\Console\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/console',
        ),
        'Symfony\\Bridge\\PsrHttpMessage\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/psr-http-message-bridge',
        ),
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'SendinBlue\\Client\\' => 
        array (
            0 => __DIR__ . '/..' . '/sendinblue/api-v3-sdk/lib',
        ),
        'Qcloud\\Sms\\' => 
        array (
            0 => __DIR__ . '/..' . '/qcloudsms/qcloudsms_php/src',
        ),
        'QL\\' => 
        array (
            0 => __DIR__ . '/..' . '/jaeger/querylist',
        ),
        'Psy\\' => 
        array (
            0 => __DIR__ . '/..' . '/psy/psysh/src/Psy',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'PhpParser\\' => 
        array (
            0 => __DIR__ . '/..' . '/nikic/php-parser/lib/PhpParser',
        ),
        'Phalcon\\' => 
        array (
            0 => __DIR__ . '/..' . '/phalcon/devtools/scripts/Phalcon',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Overtrue\\Socialite\\' => 
        array (
            0 => __DIR__ . '/..' . '/overtrue/socialite/src',
        ),
        'Omnipay\\Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/omnipay/stripe/src',
        ),
        'Omnipay\\PayPal\\' => 
        array (
            0 => __DIR__ . '/..' . '/omnipay/paypal/src',
        ),
        'Omnipay\\Common\\' => 
        array (
            0 => __DIR__ . '/..' . '/omnipay/common/src/Common',
        ),
        'OSS\\' => 
        array (
            0 => __DIR__ . '/..' . '/aliyuncs/oss-sdk-php/src/OSS',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Money\\' => 
        array (
            0 => __DIR__ . '/..' . '/moneyphp/money/src',
        ),
        'JPush\\' => 
        array (
            0 => __DIR__ . '/..' . '/jpush/jpush/src/JPush',
        ),
        'Http\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-http/promise/src',
        ),
        'Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-http/message/src',
            1 => __DIR__ . '/..' . '/php-http/message-factory/src',
        ),
        'Http\\Discovery\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-http/discovery/src',
        ),
        'Http\\Client\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-http/httplug/src',
        ),
        'Http\\Adapter\\Guzzle6\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-http/guzzle6-adapter/src',
        ),
        'GuzzleHttp\\Psr7\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'GuzzleHttp\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/promises/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
        'Endroid\\QrCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/endroid/qrcode/src',
        ),
        'EasyWeChat\\' => 
        array (
            0 => __DIR__ . '/..' . '/overtrue/wechat/src',
        ),
        'Curl\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-curl-class/php-curl-class/src/Curl',
        ),
        'Clue\\StreamFilter\\' => 
        array (
            0 => __DIR__ . '/..' . '/clue/stream-filter/src',
        ),
        'Buuum\\' => 
        array (
            0 => __DIR__ . '/..' . '/buuum/redsys/src/Redsys',
        ),
        'Braintree\\' => 
        array (
            0 => __DIR__ . '/..' . '/braintree/braintree_php/lib/Braintree',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Pimple' => 
            array (
                0 => __DIR__ . '/..' . '/pimple/pimple/src',
            ),
        ),
        'J' => 
        array (
            'JakubOnderka\\PhpConsoleHighlighter' => 
            array (
                0 => __DIR__ . '/..' . '/jakub-onderka/php-console-highlighter/src',
            ),
            'JakubOnderka\\PhpConsoleColor' => 
            array (
                0 => __DIR__ . '/..' . '/jakub-onderka/php-console-color/src',
            ),
        ),
        'G' => 
        array (
            'Gregwar\\Image' => 
            array (
                0 => __DIR__ . '/..' . '/gregwar/image',
            ),
            'Gregwar\\Cache' => 
            array (
                0 => __DIR__ . '/..' . '/gregwar/cache',
            ),
        ),
        'F' => 
        array (
            'Fabfuel\\Prophiler\\' => 
            array (
                0 => __DIR__ . '/..' . '/fabfuel/prophiler/src',
            ),
        ),
        'D' => 
        array (
            'Doctrine\\Common\\Cache\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/cache/lib',
            ),
        ),
        'B' => 
        array (
            'Braintree' => 
            array (
                0 => __DIR__ . '/..' . '/braintree/braintree_php/lib',
            ),
        ),
    );

    public static $classMap = array (
        'ArithmeticError' => __DIR__ . '/..' . '/symfony/polyfill-php70/Resources/stubs/ArithmeticError.php',
        'AssertionError' => __DIR__ . '/..' . '/symfony/polyfill-php70/Resources/stubs/AssertionError.php',
        'Callback' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'CallbackBody' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'CallbackParam' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'CallbackParameterToReference' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'CallbackReturnReference' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'CallbackReturnValue' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'DOMDocumentWrapper' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'DOMEvent' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'DivisionByZeroError' => __DIR__ . '/..' . '/symfony/polyfill-php70/Resources/stubs/DivisionByZeroError.php',
        'Error' => __DIR__ . '/..' . '/symfony/polyfill-php70/Resources/stubs/Error.php',
        'Fabfuel\\Prophiler\\Adapter\\AdapterAbstractTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Adapter/AdapterAbstractTest.php',
        'Fabfuel\\Prophiler\\Adapter\\Doctrine\\SQLLoggerTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Adapter/Doctrine/SQLLoggerTest.php',
        'Fabfuel\\Prophiler\\Adapter\\Fabfuel\\MongoTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Adapter/Fabfuel/MongoTest.php',
        'Fabfuel\\Prophiler\\Adapter\\Interop\\Container\\ContainerTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Adapter/Interop/Container/ContainerTest.php',
        'Fabfuel\\Prophiler\\Adapter\\Psr\\Log\\LoggerTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Adapter/Psr/Log/LoggerTest.php',
        'Fabfuel\\Prophiler\\Aggregator\\AbstractAggregatorTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Aggregator/AbstractAggregatorTest.php',
        'Fabfuel\\Prophiler\\Aggregator\\AggregationTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Aggregator/AggregationTest.php',
        'Fabfuel\\Prophiler\\Aggregator\\Cache\\CacheAggregatorTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Aggregator/Cache/CacheAggregatorTest.php',
        'Fabfuel\\Prophiler\\Aggregator\\Database\\QueryAggregatorTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Aggregator/Database/QueryAggregatorTest.php',
        'Fabfuel\\Prophiler\\Aggregator\\TestableAbstractAggregator' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Aggregator/AbstractAggregatorTest.php',
        'Fabfuel\\Prophiler\\BenchmarkTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Benchmark/BenchmarkTest.php',
        'Fabfuel\\Prophiler\\Benchmark\\BenchmarkFactoryTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Benchmark/BenchmarkFactoryTest.php',
        'Fabfuel\\Prophiler\\DataCollector\\GenericTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/DataCollector/GenericTest.php',
        'Fabfuel\\Prophiler\\DataCollector\\RequestTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/DataCollector/RequestTest.php',
        'Fabfuel\\Prophiler\\Decorator\\Elasticsearch\\ClientDecoratorTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Decorator/Elasticsearch/ClientDecoratorTest.php',
        'Fabfuel\\Prophiler\\Decorator\\Foobar' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Decorator/GeneralDecoratorTest.php',
        'Fabfuel\\Prophiler\\Decorator\\GeneralDecoratorTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Decorator/GeneralDecoratorTest.php',
        'Fabfuel\\Prophiler\\Decorator\\PDO\\PDOStatementTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Decorator/PDO/PDOStatementTest.php',
        'Fabfuel\\Prophiler\\Decorator\\PDO\\PDOTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Decorator/PDO/PDOTest.php',
        'Fabfuel\\Prophiler\\Decorator\\Phalcon\\Cache\\BackendDecoratorTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Decorator/Phalcon/Cache/BackendDecoratorTest.php',
        'Fabfuel\\Prophiler\\Iterator\\ComponentFilteredIteratorTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Iterator/ComponentFilteredIteratorTest.php',
        'Fabfuel\\Prophiler\\Mock\\PDO' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Mock/PDO.php',
        'Fabfuel\\Prophiler\\Plugin\\Manager\\PhalconTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Plugin/Manager/PhalconTest.php',
        'Fabfuel\\Prophiler\\Plugin\\Phalcon\\Db\\AdapterPluginTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Plugin/Phalcon/Db/AdapterPluginTest.php',
        'Fabfuel\\Prophiler\\Plugin\\Phalcon\\Mvc\\DispatcherPluginTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Plugin/Phalcon/Mvc/DispatcherPluginTest.php',
        'Fabfuel\\Prophiler\\Plugin\\Phalcon\\Mvc\\ViewPluginTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Plugin/Phalcon/Mvc/ViewPluginTest.php',
        'Fabfuel\\Prophiler\\Plugin\\Phalcon\\PhalconPluginTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Plugin/Phalcon/PhalconPluginTest.php',
        'Fabfuel\\Prophiler\\ProfilerTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/ProfilerTest.php',
        'Fabfuel\\Prophiler\\ToolbarTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/ToolbarTest.php',
        'Fabfuel\\Prophiler\\Toolbar\\Formatter\\BenchmarkFormatterTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Toolbar/Formatter/BenchmarkFormatterTest.php',
        'Fabfuel\\Prophiler\\Toolbar\\Formatter\\Encoder\\HtmlEncoderTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Toolbar/Formatter/Encoder/HtmlEncoderTest.php',
        'Fabfuel\\Prophiler\\Toolbar\\Formatter\\LogFormatterTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Toolbar/Formatter/LogFormatterTest.php',
        'Fabfuel\\Prophiler\\Toolbar\\Formatter\\TimelineFormatterTest' => __DIR__ . '/..' . '/fabfuel/prophiler/tests/Fabfuel/Prophiler/Toolbar/Formatter/TimelineFormatterTest.php',
        'ICallbackNamed' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'Logger' => __DIR__ . '/..' . '/apache/log4php/src/main/php/Logger.php',
        'LoggerAppender' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerAppender.php',
        'LoggerAppenderConsole' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderConsole.php',
        'LoggerAppenderDailyFile' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderDailyFile.php',
        'LoggerAppenderEcho' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderEcho.php',
        'LoggerAppenderFile' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderFile.php',
        'LoggerAppenderFirePHP' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderFirePHP.php',
        'LoggerAppenderMail' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderMail.php',
        'LoggerAppenderMailEvent' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderMailEvent.php',
        'LoggerAppenderMongoDB' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderMongoDB.php',
        'LoggerAppenderNull' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderNull.php',
        'LoggerAppenderPDO' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderPDO.php',
        'LoggerAppenderPhp' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderPhp.php',
        'LoggerAppenderPool' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerAppenderPool.php',
        'LoggerAppenderRollingFile' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderRollingFile.php',
        'LoggerAppenderSocket' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderSocket.php',
        'LoggerAppenderSyslog' => __DIR__ . '/..' . '/apache/log4php/src/main/php/appenders/LoggerAppenderSyslog.php',
        'LoggerAutoloader' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerAutoloader.php',
        'LoggerConfigurable' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerConfigurable.php',
        'LoggerConfigurationAdapter' => __DIR__ . '/..' . '/apache/log4php/src/main/php/configurators/LoggerConfigurationAdapter.php',
        'LoggerConfigurationAdapterINI' => __DIR__ . '/..' . '/apache/log4php/src/main/php/configurators/LoggerConfigurationAdapterINI.php',
        'LoggerConfigurationAdapterPHP' => __DIR__ . '/..' . '/apache/log4php/src/main/php/configurators/LoggerConfigurationAdapterPHP.php',
        'LoggerConfigurationAdapterXML' => __DIR__ . '/..' . '/apache/log4php/src/main/php/configurators/LoggerConfigurationAdapterXML.php',
        'LoggerConfigurator' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerConfigurator.php',
        'LoggerConfiguratorDefault' => __DIR__ . '/..' . '/apache/log4php/src/main/php/configurators/LoggerConfiguratorDefault.php',
        'LoggerException' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerException.php',
        'LoggerFilter' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerFilter.php',
        'LoggerFilterDenyAll' => __DIR__ . '/..' . '/apache/log4php/src/main/php/filters/LoggerFilterDenyAll.php',
        'LoggerFilterLevelMatch' => __DIR__ . '/..' . '/apache/log4php/src/main/php/filters/LoggerFilterLevelMatch.php',
        'LoggerFilterLevelRange' => __DIR__ . '/..' . '/apache/log4php/src/main/php/filters/LoggerFilterLevelRange.php',
        'LoggerFilterStringMatch' => __DIR__ . '/..' . '/apache/log4php/src/main/php/filters/LoggerFilterStringMatch.php',
        'LoggerFormattingInfo' => __DIR__ . '/..' . '/apache/log4php/src/main/php/helpers/LoggerFormattingInfo.php',
        'LoggerHierarchy' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerHierarchy.php',
        'LoggerLayout' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerLayout.php',
        'LoggerLayoutHtml' => __DIR__ . '/..' . '/apache/log4php/src/main/php/layouts/LoggerLayoutHtml.php',
        'LoggerLayoutPattern' => __DIR__ . '/..' . '/apache/log4php/src/main/php/layouts/LoggerLayoutPattern.php',
        'LoggerLayoutSerialized' => __DIR__ . '/..' . '/apache/log4php/src/main/php/layouts/LoggerLayoutSerialized.php',
        'LoggerLayoutSimple' => __DIR__ . '/..' . '/apache/log4php/src/main/php/layouts/LoggerLayoutSimple.php',
        'LoggerLayoutTTCC' => __DIR__ . '/..' . '/apache/log4php/src/main/php/layouts/LoggerLayoutTTCC.php',
        'LoggerLayoutXml' => __DIR__ . '/..' . '/apache/log4php/src/main/php/layouts/LoggerLayoutXml.php',
        'LoggerLevel' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerLevel.php',
        'LoggerLocationInfo' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerLocationInfo.php',
        'LoggerLoggingEvent' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerLoggingEvent.php',
        'LoggerMDC' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerMDC.php',
        'LoggerNDC' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerNDC.php',
        'LoggerOptionConverter' => __DIR__ . '/..' . '/apache/log4php/src/main/php/helpers/LoggerOptionConverter.php',
        'LoggerPatternConverter' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverter.php',
        'LoggerPatternConverterClass' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterClass.php',
        'LoggerPatternConverterCookie' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterCookie.php',
        'LoggerPatternConverterDate' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterDate.php',
        'LoggerPatternConverterEnvironment' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterEnvironment.php',
        'LoggerPatternConverterFile' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterFile.php',
        'LoggerPatternConverterLevel' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterLevel.php',
        'LoggerPatternConverterLine' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterLine.php',
        'LoggerPatternConverterLiteral' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterLiteral.php',
        'LoggerPatternConverterLocation' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterLocation.php',
        'LoggerPatternConverterLogger' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterLogger.php',
        'LoggerPatternConverterMDC' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterMDC.php',
        'LoggerPatternConverterMessage' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterMessage.php',
        'LoggerPatternConverterMethod' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterMethod.php',
        'LoggerPatternConverterNDC' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterNDC.php',
        'LoggerPatternConverterNewLine' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterNewLine.php',
        'LoggerPatternConverterProcess' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterProcess.php',
        'LoggerPatternConverterRelative' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterRelative.php',
        'LoggerPatternConverterRequest' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterRequest.php',
        'LoggerPatternConverterServer' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterServer.php',
        'LoggerPatternConverterSession' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterSession.php',
        'LoggerPatternConverterSessionID' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterSessionID.php',
        'LoggerPatternConverterSuperglobal' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterSuperglobal.php',
        'LoggerPatternConverterThrowable' => __DIR__ . '/..' . '/apache/log4php/src/main/php/pattern/LoggerPatternConverterThrowable.php',
        'LoggerPatternParser' => __DIR__ . '/..' . '/apache/log4php/src/main/php/helpers/LoggerPatternParser.php',
        'LoggerReflectionUtils' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerReflectionUtils.php',
        'LoggerRenderer' => __DIR__ . '/..' . '/apache/log4php/src/main/php/renderers/LoggerRenderer.php',
        'LoggerRendererDefault' => __DIR__ . '/..' . '/apache/log4php/src/main/php/renderers/LoggerRendererDefault.php',
        'LoggerRendererException' => __DIR__ . '/..' . '/apache/log4php/src/main/php/renderers/LoggerRendererException.php',
        'LoggerRendererMap' => __DIR__ . '/..' . '/apache/log4php/src/main/php/renderers/LoggerRendererMap.php',
        'LoggerRoot' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerRoot.php',
        'LoggerThrowableInformation' => __DIR__ . '/..' . '/apache/log4php/src/main/php/LoggerThrowableInformation.php',
        'LoggerUtils' => __DIR__ . '/..' . '/apache/log4php/src/main/php/helpers/LoggerUtils.php',
        'OLERead' => __DIR__ . '/..' . '/nuovo/spreadsheet-reader/php-excel-reader/excel_reader2.php',
        'Omnipay\\Omnipay' => __DIR__ . '/..' . '/omnipay/common/src/Omnipay.php',
        'ParseError' => __DIR__ . '/..' . '/symfony/polyfill-php70/Resources/stubs/ParseError.php',
        'SessionUpdateTimestampHandlerInterface' => __DIR__ . '/..' . '/symfony/polyfill-php70/Resources/stubs/SessionUpdateTimestampHandlerInterface.php',
        'SpreadsheetReader' => __DIR__ . '/..' . '/nuovo/spreadsheet-reader/SpreadsheetReader.php',
        'SpreadsheetReader_CSV' => __DIR__ . '/..' . '/nuovo/spreadsheet-reader/SpreadsheetReader_CSV.php',
        'SpreadsheetReader_ODS' => __DIR__ . '/..' . '/nuovo/spreadsheet-reader/SpreadsheetReader_ODS.php',
        'SpreadsheetReader_XLS' => __DIR__ . '/..' . '/nuovo/spreadsheet-reader/SpreadsheetReader_XLS.php',
        'SpreadsheetReader_XLSX' => __DIR__ . '/..' . '/nuovo/spreadsheet-reader/SpreadsheetReader_XLSX.php',
        'Spreadsheet_Excel_Reader' => __DIR__ . '/..' . '/nuovo/spreadsheet-reader/php-excel-reader/excel_reader2.php',
        'TypeError' => __DIR__ . '/..' . '/symfony/polyfill-php70/Resources/stubs/TypeError.php',
        'XLSXWriter' => __DIR__ . '/..' . '/mk-j/php_xlsxwriter/xlsxwriter.class.php',
        'XLSXWriter_BuffererWriter' => __DIR__ . '/..' . '/mk-j/php_xlsxwriter/xlsxwriter.class.php',
        'phpQuery' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'phpQueryEvents' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'phpQueryObject' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
        'phpQueryPlugins' => __DIR__ . '/..' . '/jaeger/phpquery-single/phpQuery.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb62cb559aeb32b7fdd59aa3e707ec665::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb62cb559aeb32b7fdd59aa3e707ec665::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitb62cb559aeb32b7fdd59aa3e707ec665::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitb62cb559aeb32b7fdd59aa3e707ec665::$classMap;

        }, null, ClassLoader::class);
    }
}
