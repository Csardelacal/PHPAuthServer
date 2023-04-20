<?php

use spitfire\autoload\AutoLoad;


#Define default classes and their locations
AutoLoad::registerClass('Controller',                                           SPITFIRE_BASEDIR.'/mvc/controller.php');

AutoLoad::registerClass('Time',                                                 SPITFIRE_BASEDIR.'/time.php');
AutoLoad::registerClass('Image',                                                SPITFIRE_BASEDIR.'/image.php');

#Database related imports
AutoLoad::registerClass('Pagination',                                           SPITFIRE_BASEDIR.'/db/pagination.php');

AutoLoad::registerClass('Schema',                                               SPITFIRE_BASEDIR.'/model/model.php');
AutoLoad::registerClass('spitfire\model\Field',                                 SPITFIRE_BASEDIR.'/model/field.php');
AutoLoad::registerClass('IntegerField',                                         SPITFIRE_BASEDIR.'/model/fields/integer.php');
AutoLoad::registerClass('FloatField',                                           SPITFIRE_BASEDIR.'/model/fields/float.php');
AutoLoad::registerClass('FileField',                                            SPITFIRE_BASEDIR.'/model/fields/file.php');
AutoLoad::registerClass('TextField',                                            SPITFIRE_BASEDIR.'/model/fields/text.php');
AutoLoad::registerClass('StringField',                                          SPITFIRE_BASEDIR.'/model/fields/string.php');
AutoLoad::registerClass('EnumField',                                            SPITFIRE_BASEDIR.'/model/fields/enum.php');
AutoLoad::registerClass('BooleanField',                                         SPITFIRE_BASEDIR.'/model/fields/boolean.php');
AutoLoad::registerClass('DatetimeField',                                        SPITFIRE_BASEDIR.'/model/fields/datetime.php');
AutoLoad::registerClass('ManyToManyField',                                      SPITFIRE_BASEDIR.'/model/fields/manytomany.php');
AutoLoad::registerClass('Reference',                                            SPITFIRE_BASEDIR.'/model/reference.php');
AutoLoad::registerClass('ChildrenField',                                        SPITFIRE_BASEDIR.'/model/children.php');

AutoLoad::registerClass('spitfire\model\adapters\ManyToManyAdapter',            SPITFIRE_BASEDIR.'/model/adapters/m2madapter.php');
AutoLoad::registerClass('spitfire\model\adapters\BridgeAdapter',                SPITFIRE_BASEDIR.'/model/adapters/bridgeadapter.php');
AutoLoad::registerClass('spitfire\model\adapters\ChildrenAdapter',              SPITFIRE_BASEDIR.'/model/adapters/childrenadapter.php');

AutoLoad::registerClass('spitfire\InputSanitizer',                              SPITFIRE_BASEDIR.'/security_io_sanitization.php');
AutoLoad::registerClass('CoffeeBean',                                           SPITFIRE_BASEDIR.'/io/beans/coffeebean.php');
AutoLoad::registerClass('spitfire\io\beans\Field',                              SPITFIRE_BASEDIR.'/io/beans/field.php');
AutoLoad::registerClass('spitfire\io\beans\BasicField',                         SPITFIRE_BASEDIR.'/io/beans/basic_field.php');
AutoLoad::registerClass('spitfire\io\beans\TextField',                          SPITFIRE_BASEDIR.'/io/beans/text_field.php');
AutoLoad::registerClass('spitfire\io\beans\IntegerField',                       SPITFIRE_BASEDIR.'/io/beans/integer_field.php');
AutoLoad::registerClass('spitfire\io\beans\LongTextField',                      SPITFIRE_BASEDIR.'/io/beans/long_text_field.php');
AutoLoad::registerClass('spitfire\io\beans\EnumField',                          SPITFIRE_BASEDIR.'/io/beans/enum_field.php');
AutoLoad::registerClass('spitfire\io\beans\BooleanField',                       SPITFIRE_BASEDIR.'/io/beans/boolean_field.php');
AutoLoad::registerClass('spitfire\io\beans\ReferenceField',                     SPITFIRE_BASEDIR.'/io/beans/reference_field.php');
AutoLoad::registerClass('spitfire\io\beans\ManyToManyField',                    SPITFIRE_BASEDIR.'/io/beans/manytomany_field.php');
AutoLoad::registerClass('spitfire\io\beans\FileField',                          SPITFIRE_BASEDIR.'/io/beans/file_field.php');
AutoLoad::registerClass('spitfire\io\beans\DateTimeField',                      SPITFIRE_BASEDIR.'/io/beans/datetime_field.php');
AutoLoad::registerClass('spitfire\io\beans\ChildBean',                          SPITFIRE_BASEDIR.'/io/beans/childbean.php');
AutoLoad::registerClass('spitfire\io\beans\renderers\Renderer',                 SPITFIRE_BASEDIR.'/io/beans/renderers/renderer.php');
AutoLoad::registerClass('spitfire\io\beans\renderers\SimpleBeanRenderer',       SPITFIRE_BASEDIR.'/io/beans/renderers/simpleBeanRenderer.php');
AutoLoad::registerClass('spitfire\io\beans\renderers\SimpleFieldRenderer',      SPITFIRE_BASEDIR.'/io/beans/renderers/simpleFieldRenderer.php');
AutoLoad::registerClass('_SF_Invoke',                                           SPITFIRE_BASEDIR.'/mvc/invoke.php');


AutoLoad::registerClass('spitfire\io\html\HTMLElement',                         SPITFIRE_BASEDIR.'/io/html/element.php');
AutoLoad::registerClass('spitfire\io\html\HTMLUnclosedElement',                 SPITFIRE_BASEDIR.'/io/html/unclosed.php');
AutoLoad::registerClass('spitfire\io\html\HTMLDiv',                             SPITFIRE_BASEDIR.'/io/html/div.php');
AutoLoad::registerClass('spitfire\io\html\HTMLSpan',                            SPITFIRE_BASEDIR.'/io/html/span.php');
AutoLoad::registerClass('spitfire\io\html\HTMLInput',                           SPITFIRE_BASEDIR.'/io/html/input.php');
AutoLoad::registerClass('spitfire\io\html\HTMLTextArea',                        SPITFIRE_BASEDIR.'/io/html/textarea.php');
AutoLoad::registerClass('spitfire\io\html\HTMLSelect',                          SPITFIRE_BASEDIR.'/io/html/select.php');
AutoLoad::registerClass('spitfire\io\html\HTMLOption',                          SPITFIRE_BASEDIR.'/io/html/option.php');
AutoLoad::registerClass('spitfire\io\html\HTMLLabel',                           SPITFIRE_BASEDIR.'/io/html/label.php');
AutoLoad::registerClass('spitfire\io\html\HTMLForm',                            SPITFIRE_BASEDIR.'/io/html/form.php');
AutoLoad::registerClass('spitfire\io\html\HTMLTable',                           SPITFIRE_BASEDIR.'/io/html/table.php');
AutoLoad::registerClass('spitfire\io\html\HTMLTableRow',                        SPITFIRE_BASEDIR.'/io/html/table_row.php');
AutoLoad::registerClass('spitfire\io\html\dateTimePicker',                      SPITFIRE_BASEDIR.'/io/html/date_picker.php');

AutoLoad::registerClass('Strings',                                              SPITFIRE_BASEDIR.'/Strings.php');

AutoLoad::registerClass('spitfire\registry\Registry',                           SPITFIRE_BASEDIR.'/io/registry/registry.php');
AutoLoad::registerClass('spitfire\registry\JSRegistry',                         SPITFIRE_BASEDIR.'/io/registry/jsregistry.php');
AutoLoad::registerClass('spitfire\registry\CSSRegistry',                        SPITFIRE_BASEDIR.'/io/registry/cssregistry.php');

AutoLoad::registerClass('Pluggable',                                            SPITFIRE_BASEDIR.'/plugins/pluggable.php');

AutoLoad::registerClass('URL',                                                  SPITFIRE_BASEDIR.'/url.php');
AutoLoad::registerClass('AbsoluteURL',                                          SPITFIRE_BASEDIR.'/AbsoluteURL.php');
AutoLoad::registerClass('spitfire\Context',                                     SPITFIRE_BASEDIR.'/core/context.php');

AutoLoad::registerClass('Email',                                                SPITFIRE_BASEDIR.'/mail.php');
