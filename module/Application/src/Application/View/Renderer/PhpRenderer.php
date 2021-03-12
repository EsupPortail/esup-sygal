<?php

namespace Application\View\Renderer;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\These;
use Application\Entity\Db\Validation;
use Application\Entity\Db\VWorkflow;
use Application\View\Helper\EcoleDoctoraleHelper;
use Application\View\Helper\EscapeTextHelper;
use Application\View\Helper\FinancementFormatterHelper;
use Application\View\Helper\StructureSubstitHelper;
use Application\View\Helper\Url\UrlTheseHelper;
use Application\View\Helper\ValidationViewHelper;
use Application\View\Helper\Workflow\RoadmapHelper;
use Application\View\Helper\Workflow\WorkflowHelper;
use Application\View\Helper\Workflow\WorkflowStepHelper;
use UnicaenApp\Message\View\Helper\MessageHelper;
use UnicaenApp\View\Helper\AppInfos;

/**
 * Description of PhpRenderer
 *
 * Permet d'utiliser les aides de vue avec de l'auto-complétion et de rendre le Refactoring des aides de vues efficace
 *
 * @method \Zend\View\Helper\Cycle                                   cycle(array $data = [], $name = 'default')
 * @method \Zend\View\Helper\DeclareVars                             declarevars()
 * @method \Zend\View\Helper\EscapeHtml                              escapeHtml($value, $recurse = 0)
 * @method \Zend\View\Helper\EscapeHtmlAttr                          escapehtmlattr($value, $recurse = 0)
 * @method \Zend\View\Helper\EscapeJs                                escapejs($value, $recurse = 0)
 * @method \Zend\View\Helper\EscapeCss                               escapecss($value, $recurse = 0)
 * @method \Zend\View\Helper\EscapeUrl                               escapeurl($value, $recurse = 0)
 * @method \Zend\View\Helper\Gravatar                                gravatar($email = '', $options = [], $attribs = [])
 * @method \Zend\View\Helper\HtmlTag                                 htmltag(array $attribs = [])
 * @method \Zend\View\Helper\HeadMeta                                headmeta($content = null, $keyValue = null, $keyType = 'name', $modifiers = [], $placement = 'APPEND')
 * @method \Zend\View\Helper\HeadStyle                               headstyle($content = null, $placement = 'APPEND', $attributes = [])
 * @method \Zend\View\Helper\HeadTitle                               headTitle($title = null, $setType = null)
 * @method string                                                    htmlflash($data, array $attribs = [], array $params = [], $content = null)
 * @method \Zend\View\Helper\HtmlList                                htmllist(array $items, $ordered = false, $attribs = false, $escape = true)
 * @method string                                                    htmlobject($data = null, $type = null, array $attribs = [], array $params = [], $content = null)
 * @method string                                                    htmlpage($data, array $attribs = [], array $params = [], $content = null)
 * @method string                                                    htmlquicktime($data, array $attribs = [], array $params = [], $content = null)
 * @method \Zend\View\Helper\Json                                    json($data, array $jsonOptions = [])
 * @method \Zend\View\Helper\Layout                                  layout($template = null)
 * @method string                                                    paginationControl(\Zend\Paginator\Paginator $paginator = null, $scrollingStyle = null, $partial = null, $params = null)
 * @method string                                                    partialloop($name = null, $values = null)
 * @method \Zend\View\Helper\Partial|string                          partial($name = null, $values = null)
 * @method \Zend\View\Helper\Placeholder                             placeholder($name = null)
 * @method string                                                    renderchildmodel($child)
 * @method \Zend\View\Helper\RenderToPlaceholder                     rendertoplaceholder($script, $placeholder)
 * @method string                                                    serverurl($requestUri = null)
 * @method \Zend\View\Helper\ViewModel                               viewmodel()
 * @method \Zend\Form\View\Helper\FormButton                         formbutton(\Zend\Form\ElementInterface $element = null, $buttonContent = null)
 * @method \Zend\Form\View\Helper\FormCaptcha                        formcaptcha(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    captchadumb(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    captchafiglet(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    captchaimage(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    captcharecaptcha(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormCheckbox                       formcheckbox(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormCollection                     formcollection(\Zend\Form\ElementInterface $element = null, $wrap = true)
 * @method \Zend\Form\View\Helper\FormColor                          formcolor(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormDateTime                       formdatetime(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormDateTimeLocal                  formdatetimelocal(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    formdatetimeselect(\Zend\Form\ElementInterface $element = null, $dateType = 1, $timeType = 1, $locale = null)
 * @method \Zend\Form\View\Helper\FormDateSelect                     formdateselect(\Zend\Form\ElementInterface $element = null, $dateType = 1, $locale = null)
 * @method \Zend\Form\View\Helper\FormElement|string                 formElement(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormElementErrors                  formelementerrors(\Zend\Form\ElementInterface $element = null, array $attributes = [])
 * @method \Zend\Form\View\Helper\FormEmail                          formemail(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormFile                           formfile(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    formfileapcprogress(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    formfilesessionprogress(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    formfileuploadprogress(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormHidden|string                  formhidden(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormImage                          formimage(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormInput                          forminput(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormLabel                          formlabel(\Zend\Form\ElementInterface $element = null, $labelContent = null, $position = null)
 * @method \Zend\Form\View\Helper\FormMonth                          formmonth(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormMonthSelect                    formmonthselect(\Zend\Form\ElementInterface $element = null, $dateType = 1, $locale = null)
 * @method \Zend\Form\View\Helper\FormMultiCheckbox                  formmulticheckbox(\Zend\Form\ElementInterface $element = null, $labelPosition = null)
 * @method \Zend\Form\View\Helper\FormNumber                         formnumber(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormPassword                       formpassword(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormRadio                          formradio(\Zend\Form\ElementInterface $element = null, $labelPosition = null)
 * @method \Zend\Form\View\Helper\FormRange                          formrange(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormReset                          formreset(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormRow                            formrow(\Zend\Form\ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $partial = null)
 * @method \Zend\Form\View\Helper\FormSearch                         formsearch(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormSelect                         formselect(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormSubmit                         formsubmit(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormTel                            formtel(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormText                           formtext(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormTextarea                       formtextarea(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormTime                           formtime(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormUrl                            formurl(\Zend\Form\ElementInterface $element = null)
 * @method \Zend\Form\View\Helper\FormWeek                           formweek(\Zend\Form\ElementInterface $element = null)
 * @method string                                                    currencyformat($number, $currencyCode = null, $showDecimals = null, $locale = null, $pattern = null)
 * @method string                                                    dateformat($date, $dateType = -1, $timeType = -1, $locale = null, $pattern = null)
 * @method string                                                    numberformat($number, $formatStyle = null, $formatType = null, $locale = null, $decimals = null)
 * @method string                                                    plural($strings, $number)
 * @method string                                                    translate($message, $textDomain = null, $locale = null)
 * @method string                                                    translateplural($singular, $plural, $number, $textDomain = null, $locale = null)
 * @method string                                                    zenddevelopertoolstime($time, $precision = 2)
 * @method string                                                    zenddevelopertoolsmemory($size, $precision = 2)
 * @method string                                                    zenddevelopertoolsdetailarray($label, array $details, $redundant = false)
 * @method \UnicaenAuth\View\Helper\AppConnection                    appconnection()
 * @method \UnicaenApp\View\Helper\Messenger                         messenger()
 * @method string                                                    modalajaxdialog($dialogDivId = null)
 * @method \UnicaenApp\View\Helper\ConfirmHelper                     confirm($message = null)
 * @method \UnicaenApp\View\Helper\ToggleDetails                     toggledetails($detailsDivId, $title = null, $iconClass = null)
 * @method string                                                    multipageformfieldset()
 * @method string                                                    multipageformnav(\UnicaenApp\Form\Element\MultipageFormNav $element)
 * @method \UnicaenApp\Form\View\Helper\MultipageFormRow             multipageformrow(\Zend\Form\ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $partial = null)
 * @method string                                                    multipageformrecap()
 * @method \UnicaenApp\Form\View\Helper\FormControlGroup|string      formControlGroup(\Zend\Form\ElementInterface $element = null, $pluginClass = 'formElement')
 * @method \UnicaenApp\Form\View\Helper\FormDate                     formDate(\UnicaenApp\Form\Element\Date $element = null, $dateReadonly = false)
 * @method \UnicaenApp\Form\View\Helper\FormDateInfSup               formdateinfsup(\UnicaenApp\Form\Element\DateInfSup $element = null, $dateInfReadonly = false, $dateSupReadonly = false)
 * @method \UnicaenApp\Form\View\Helper\FormRowDateInfSup            formrowdateinfsup(\Zend\Form\ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $partial = null)
 * @method \UnicaenApp\Form\View\Helper\FormSearchAndSelect          formsearchandselect(\Zend\Form\ElementInterface $element = null)
 * @method \UnicaenApp\Form\View\Helper\FormLdapPeople               formLdapPeople(\Zend\Form\ElementInterface $element = null)
 * @method \UnicaenApp\Form\View\Helper\FormErrors|string            formErrors(\Zend\Form\Form $form = null, $message = null)
 * @method \UnicaenApp\Form\View\Helper\Form                         form(\Zend\Form\FormInterface $form = null)
 * @method \UnicaenApp\View\Helper\MessageCollectorHelper            messagecollector($namespace = null)
 * @method \UnicaenApp\View\Helper\HeadScript                        headScript($mode = 'FILE', $spec = null, $placement = 'APPEND', array $attrs = [], $type = 'text/javascript')
 * @method \UnicaenApp\View\Helper\InlineScript                      inlineScript($mode = 'FILE', $spec = null, $placement = 'APPEND', array $attrs = [], $type = 'text/javascript')
 * @method \UnicaenApp\View\Helper\HeadLink                          headLink(array $attributes = null, $placement = 'APPEND')
 * @method \UnicaenApp\Form\View\Helper\FormAdvancedMultiCheckbox    formadvancedmulticheckbox(\Zend\Form\ElementInterface $element = null, $labelPosition = null)
 * @method \UnicaenApp\View\Helper\HistoriqueViewHelper              historique(\UnicaenApp\Entity\HistoriqueAwareInterface $entity = null)
 * @method \UnicaenApp\View\Helper\TabAjax\TabAjaxViewHelper         tabajax($tabs = null)
 * @method \UnicaenApp\View\Helper\TagViewHelper                     tag($name = null, array $attributes = [])
 * @method MessageHelper                                             message()
 * @method AppInfos                                                  appInfos()
 *
 * @method boolean isAllowed($resource, $privilege = null)
 *
 * @method array                                              queryParams()
 * @method \Application\View\Helper\Uploader\UploaderHelper   uploader()
 * @method \Application\View\Helper\Sortable                  sortable($sort)
 * @method string                                             filterPanel($config)
 * @method string                                             selectsFilterPanel($config)
 * @method string                                             filtersPanel($config)
 * @method EscapeTextHelper                                   escapeText($value = null)
 *
 * @method WorkflowHelper        wf(These $these = null)
 * @method WorkflowStepHelper    wfs(VWorkflow $step = null)
 * @method RoadmapHelper         roadmap(These $these = null)
 * @method ValidationViewHelper  validation(Validation $validation = null)
 * @method EcoleDoctoraleHelper  ed(EcoleDoctorale $ecole)
 *
 * @method UrlTheseHelper urlThese()
 * @method StructureSubstitHelper structureSubstitHelper()
 * @method FinancementFormatterHelper financementFormatter()
 *
 * @author UnicaenCode
 */
class PhpRenderer extends \Zend\View\Renderer\PhpRenderer {



}