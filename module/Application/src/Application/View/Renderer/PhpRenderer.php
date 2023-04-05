<?php

namespace Application\View\Renderer;

use Depot\View\Helper\Url\UrlDepotHelper;
use Structure\Entity\Db\EcoleDoctorale;
use These\Entity\Db\These;
use Application\Entity\Db\Validation;
use Depot\Entity\Db\VWorkflow;
use Application\View\Helper\Actualite\ActualiteViewHelper;
use Structure\View\Helper\EcoleDoctoraleHelper;
use Application\View\Helper\EscapeTextHelper;
use Application\View\Helper\FinancementFormatterHelper;
use Structure\View\Helper\StructureSubstitHelper;
use These\View\Helper\Url\UrlTheseHelper;
use Application\View\Helper\ValidationViewHelper;
use Depot\View\Helper\Workflow\RoadmapHelper;
use Depot\View\Helper\Workflow\WorkflowHelper;
use Depot\View\Helper\Workflow\WorkflowStepHelper;
use UnicaenApp\Message\View\Helper\MessageHelper;
use UnicaenApp\View\Helper\AppInfos;

/**
 * Description of PhpRenderer
 *
 * Permet d'utiliser les aides de vue avec de l'auto-complétion et de rendre le Refactoring des aides de vues efficace
 *
 * @method \Laminas\View\Helper\Cycle                                   cycle(array $data = [], $name = 'default')
 * @method \Laminas\View\Helper\DeclareVars                             declarevars()
 * @method \Laminas\View\Helper\EscapeHtml                              escapeHtml($value, $recurse = 0)
 * @method \Laminas\View\Helper\EscapeHtmlAttr                          escapehtmlattr($value, $recurse = 0)
 * @method \Laminas\View\Helper\EscapeJs                                escapejs($value, $recurse = 0)
 * @method \Laminas\View\Helper\EscapeCss                               escapecss($value, $recurse = 0)
 * @method \Laminas\View\Helper\EscapeUrl                               escapeurl($value, $recurse = 0)
 * @method \Laminas\View\Helper\Gravatar                                gravatar($email = '', $options = [], $attribs = [])
 * @method \Laminas\View\Helper\HtmlTag                                 htmltag(array $attribs = [])
 * @method \Laminas\View\Helper\HeadMeta                                headmeta($content = null, $keyValue = null, $keyType = 'name', $modifiers = [], $placement = 'APPEND')
 * @method \Laminas\View\Helper\HeadStyle                               headstyle($content = null, $placement = 'APPEND', $attributes = [])
 * @method \Laminas\View\Helper\HeadTitle                               headTitle($title = null, $setType = null)
 * @method string                                                    htmlflash($data, array $attribs = [], array $params = [], $content = null)
 * @method \Laminas\View\Helper\HtmlList                                htmllist(array $items, $ordered = false, $attribs = false, $escape = true)
 * @method string                                                    htmlobject($data = null, $type = null, array $attribs = [], array $params = [], $content = null)
 * @method string                                                    htmlpage($data, array $attribs = [], array $params = [], $content = null)
 * @method string                                                    htmlquicktime($data, array $attribs = [], array $params = [], $content = null)
 * @method \Laminas\View\Helper\Json                                    json($data, array $jsonOptions = [])
 * @method \Laminas\View\Helper\Layout                                  layout($template = null)
 * @method string                                                    paginationControl(\Laminas\Paginator\Paginator $paginator = null, $scrollingStyle = null, $partial = null, $params = null)
 * @method string                                                    partialloop($name = null, $values = null)
 * @method \Laminas\View\Helper\Partial|string                          partial($name = null, $values = null)
 * @method \Laminas\View\Helper\Placeholder                             placeholder($name = null)
 * @method string                                                    renderchildmodel($child)
 * @method \Laminas\View\Helper\RenderToPlaceholder                     rendertoplaceholder($script, $placeholder)
 * @method string                                                    serverurl($requestUri = null)
 * @method \Laminas\View\Helper\ViewModel                               viewmodel()
 * @method \Laminas\Form\View\Helper\FormButton                         formbutton(\Laminas\Form\ElementInterface $element = null, $buttonContent = null)
 * @method \Laminas\Form\View\Helper\FormCaptcha                        formcaptcha(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    captchadumb(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    captchafiglet(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    captchaimage(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    captcharecaptcha(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormCheckbox                       formcheckbox(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormCollection                     formcollection(\Laminas\Form\ElementInterface $element = null, $wrap = true)
 * @method \Laminas\Form\View\Helper\FormColor                          formcolor(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormDateTime                       formdatetime(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormDateTimeLocal                  formdatetimelocal(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    formdatetimeselect(\Laminas\Form\ElementInterface $element = null, $dateType = 1, $timeType = 1, $locale = null)
 * @method \Laminas\Form\View\Helper\FormDateSelect                     formdateselect(\Laminas\Form\ElementInterface $element = null, $dateType = 1, $locale = null)
 * @method \Laminas\Form\View\Helper\FormElement|string                 formElement(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormElementErrors                  formelementerrors(\Laminas\Form\ElementInterface $element = null, array $attributes = [])
 * @method \Laminas\Form\View\Helper\FormEmail                          formemail(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormFile                           formfile(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    formfileapcprogress(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    formfilesessionprogress(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    formfileuploadprogress(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormHidden|string                  formhidden(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormImage                          formimage(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormInput                          forminput(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormLabel                          formlabel(\Laminas\Form\ElementInterface $element = null, $labelContent = null, $position = null)
 * @method \Laminas\Form\View\Helper\FormMonth                          formmonth(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormMonthSelect                    formmonthselect(\Laminas\Form\ElementInterface $element = null, $dateType = 1, $locale = null)
 * @method \Laminas\Form\View\Helper\FormMultiCheckbox                  formmulticheckbox(\Laminas\Form\ElementInterface $element = null, $labelPosition = null)
 * @method \Laminas\Form\View\Helper\FormNumber                         formnumber(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormPassword                       formpassword(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormRadio                          formradio(\Laminas\Form\ElementInterface $element = null, $labelPosition = null)
 * @method \Laminas\Form\View\Helper\FormRange                          formrange(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormReset                          formreset(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormRow                            formrow(\Laminas\Form\ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $partial = null)
 * @method \Laminas\Form\View\Helper\FormSearch                         formsearch(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormSelect                         formselect(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormSubmit                         formsubmit(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormTel                            formtel(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormText                           formtext(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormTextarea                       formtextarea(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormTime                           formtime(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormUrl                            formurl(\Laminas\Form\ElementInterface $element = null)
 * @method \Laminas\Form\View\Helper\FormWeek                           formweek(\Laminas\Form\ElementInterface $element = null)
 * @method string                                                    currencyformat($number, $currencyCode = null, $showDecimals = null, $locale = null, $pattern = null)
 * @method string                                                    dateformat($date, $dateType = -1, $timeType = -1, $locale = null, $pattern = null)
 * @method string                                                    numberformat($number, $formatStyle = null, $formatType = null, $locale = null, $decimals = null)
 * @method string                                                    plural($strings, $number)
 * @method string                                                    translate($message, $textDomain = null, $locale = null)
 * @method string                                                    translateplural($singular, $plural, $number, $textDomain = null, $locale = null)
 * @method string                                                    LaminasDeveloperToolsTime($time, $precision = 2)
 * @method string                                                    LaminasDeveloperToolsMemory($size, $precision = 2)
 * @method string                                                    LaminasDeveloperToolsDetailArray($label, array $details, $redundant = false)
 * @method \UnicaenAuth\View\Helper\AppConnection                    appconnection()
 * @method \UnicaenApp\View\Helper\Messenger                         messenger()
 * @method string                                                    modalajaxdialog($dialogDivId = null)
 * @method \UnicaenApp\View\Helper\ConfirmHelper                     confirm($message = null)
 * @method \UnicaenApp\View\Helper\ToggleDetails                     toggledetails($detailsDivId, $title = null, $iconClass = null)
 * @method string                                                    multipageformfieldset()
 * @method string                                                    multipageformnav(\UnicaenApp\Form\Element\MultipageFormNav $element)
 * @method \UnicaenApp\Form\View\Helper\MultipageFormRow             multipageformrow(\Laminas\Form\ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $partial = null)
 * @method string                                                    multipageformrecap()
 * @method \UnicaenApp\Form\View\Helper\FormControlGroup|string      formControlGroup(\Laminas\Form\ElementInterface $element = null, $pluginClass = 'formElement')
 * @method \UnicaenApp\Form\View\Helper\FormDate                     formDate(\UnicaenApp\Form\Element\Date $element = null, $dateReadonly = false)
 * @method \UnicaenApp\Form\View\Helper\FormDateInfSup               formdateinfsup(\UnicaenApp\Form\Element\DateInfSup $element = null, $dateInfReadonly = false, $dateSupReadonly = false)
 * @method \UnicaenApp\Form\View\Helper\FormRowDateInfSup            formrowdateinfsup(\Laminas\Form\ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $partial = null)
 * @method \UnicaenApp\Form\View\Helper\FormSearchAndSelect          formsearchandselect(\Laminas\Form\ElementInterface $element = null)
 * @method \UnicaenApp\Form\View\Helper\FormLdapPeople               formLdapPeople(\Laminas\Form\ElementInterface $element = null)
 * @method \UnicaenApp\Form\View\Helper\FormErrors|string            formErrors(\Laminas\Form\Form $form = null, $message = null)
 * @method \UnicaenApp\Form\View\Helper\Form                         form(\Laminas\Form\FormInterface $form = null)
 * @method \UnicaenApp\View\Helper\MessageCollectorHelper            messagecollector($namespace = null)
 * @method \UnicaenApp\View\Helper\HeadScript                        headScript($mode = 'FILE', $spec = null, $placement = 'APPEND', array $attrs = [], $type = 'text/javascript')
 * @method \UnicaenApp\View\Helper\InlineScript                      inlineScript($mode = 'FILE', $spec = null, $placement = 'APPEND', array $attrs = [], $type = 'text/javascript')
 * @method \UnicaenApp\View\Helper\HeadLink                          headLink(array $attributes = null, $placement = 'APPEND')
 * @method \UnicaenApp\Form\View\Helper\FormAdvancedMultiCheckbox    formadvancedmulticheckbox(\Laminas\Form\ElementInterface $element = null, $labelPosition = null)
 * @method \UnicaenApp\View\Helper\HistoriqueViewHelper              historique(\UnicaenApp\Entity\HistoriqueAwareInterface $entity = null)
 * @method \UnicaenApp\View\Helper\TabAjax\TabAjaxViewHelper         tabajax($tabs = null)
 * @method \UnicaenApp\View\Helper\TagViewHelper                     tag($name = null, array $attributes = [])
 * @method MessageHelper                                             message()
 * @method AppInfos                                                  appInfos()
 *
 * @method \UnicaenAlerte\View\Helper\AlerteViewHelper alertes()
 *
 * @method \UnicaenIdref\View\Helper\IdrefPopupTriggerViewHelper idrefPopupTrigger(array $sourceElements, ?string $destinationElement = null)
 *
 * @method boolean isAllowed($resource, $privilege = null)
 *
 * @method array                                              queryParams()
 * @method \Fichier\View\Helper\Uploader\UploaderHelper   uploader()
 * @method \Application\View\Helper\Sortable                  sortable($sort)
 * @method string                                             filterPanel($config)
 * @method string                                             filtersPanel($config)
 * @method EscapeTextHelper                                   escapeText($value = null)
 *
 * @method WorkflowHelper        wf(These $these = null)
 * @method WorkflowStepHelper    wfs(VWorkflow $step = null)
 * @method RoadmapHelper         roadmap(These $these = null)
 * @method ValidationViewHelper  validation(Validation $validation = null)
 * @method EcoleDoctoraleHelper  ed(EcoleDoctorale $ecole)
 *
 * @method UrlDepotHelper urlThese()
 * @method StructureSubstitHelper structureSubstitHelper()
 * @method FinancementFormatterHelper financementFormatter()
 *
 * @method ActualiteViewHelper actualite()
 *
 * @author UnicaenCode
 */
class PhpRenderer extends \Laminas\View\Renderer\PhpRenderer {



}