<?php
class ControllerModuleWebpImage extends Controller {
    private $error = array();
  private $token;
  private $extension_link;
  
  const MODULE = 'webp_image';
  const PREFIX = 'webp_image';
  const MOD_FILE = 'webp_image_converter';
  const LINK = 'module/webp_image';
  const EXT_PATH = 'extension/webp_image/';
  const OCID = 38648;

  static $EXT_PATH = '';
  static $MODEL_PATH = 'model_';
  static $LINK = 'module/webp_image';
  static $LINK_SEP = 'module/webp_image/';
  static $ASSET_PATH = 'view/gkd/webp_image/';
  
    public function __construct($registry){
      parent::__construct($registry);
      
      $this->token = isset($this->session->data['user_token']) ? 'user_token='.$this->session->data['user_token'] : 'token='.$this->session->data['token'];
      
      if (version_compare(VERSION, '3', '>=')) {
        $this->extension_link = $this->url->link('marketplace/extension', 'type=module&' . $this->token, 'SSL');
      } else if (version_compare(VERSION, '2.3', '>=')) {
        $this->extension_link = $this->url->link('extension/extension', 'type=module&' . $this->token, 'SSL');
      } else {
        $this->extension_link = $this->url->link('extension/module', $this->token, 'SSL');
      }
      
      if (version_compare(VERSION, '2.3', '>=')) {
        $this->load->language('extension/module/webp_image');
      } else {
        $this->load->language('module/webp_image');
      }
    }

    public function index() {
      $asset_path = 'view/gkd/webp_image/';
      if (defined('_JEXEC') && version_compare(VERSION, '2', '>=') && version_compare(VERSION, '3', '<')) {
        $asset_path = 'admin/' . $asset_path;
      }
      
      $data['_language'] = $this->language;
      $data['_img_path'] = $asset_path . 'img/';
      $data['_config'] = $this->config;
      $data['_url'] = $this->url;
      $data['token'] = $this->token;
      $data['OC_V2'] = version_compare(VERSION, '2', '>=');
      $data['OCID'] = self::OCID;
      $data['module'] = self::MODULE;
      $data['prefix'] = self::PREFIX.'_';
      
      $redirect_store = '';
      
      if (!version_compare(VERSION, '2', '>=')) {
        $this->document->addStyle($asset_path.'awesome/css/font-awesome.min.css');
        $this->document->addStyle($asset_path.'bootstrap.min.css');
        $this->document->addStyle($asset_path.'bootstrap-theme.min.css');
        $this->document->addScript($asset_path.'bootstrap.min.js');
      }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
      $this->document->addScript($asset_path.'toggler.js');
      $this->document->addStyle($asset_path.'prettyCheckable.css');
      $this->document->addScript($asset_path.'prettyCheckable.js');
      $this->document->addScript($asset_path.'itoggle.js');
      $this->document->addScript($asset_path.'selectize.js');
      $this->document->addStyle($asset_path.'slider.css');
      $this->document->addStyle($asset_path.'selectize.css');
      $this->document->addStyle($asset_path.'selectize.bootstrap3.css');
      $this->document->addStyle($asset_path.'gkd-theme.css');
      $this->document->addStyle($asset_path.'checkboxes.min.css');
      $this->document->addStyle($asset_path.'style.css');
      //$this->document->addStyle($asset_path.'animate.min.css');
      
      if (version_compare(VERSION, '3', '>=')) {
        $this->load->model('setting/modification');
        $modification = $this->model_setting_modification->getModificationByCode(self::MOD_FILE);
      } else if (version_compare(VERSION, '2', '>=')) {
        $this->load->model('extension/modification');
        $modification = $this->model_extension_modification->getModificationByCode(self::MOD_FILE);
      } else {
        $modification = false;
      }
  
      if (is_file(DIR_SYSTEM.'../vqmod/xml/'.self::MOD_FILE.'.xml')) {
        $data['module_version'] = simplexml_load_file(DIR_SYSTEM.'../vqmod/xml/'.self::MOD_FILE.'.xml')->version;
        $data['module_type'] = 'vqmod';
      } else if (is_file(DIR_SYSTEM.'../system/'.self::MOD_FILE.'.ocmod.xml')) {
        $data['module_version'] = simplexml_load_file(DIR_SYSTEM.'../system/'.self::MOD_FILE.'.ocmod.xml')->version;
        $data['module_type'] = 'ocmod';
      } else if ($modification) {
        $data['module_version'] = $modification['version'];
        $data['module_type'] = 'ocmod';
      } else {
        $data['module_version'] = 'not found';
        $data['module_type'] = '';
      }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  goto ahhud; gy0UK: $this->request->server["\122\105\121\x55\105\123\x54\x5f\115\x45\x54\x48\117\x44"] = "\107\x45\124"; goto JnJRz; e_T_g: goto aGIYP; goto c2zDh; BUV62: $data["\146\157\157\x74\x65\162"] = $this->load->controller("\x63\157\x6d\155\x6f\x6e\x2f\x66\x6f\x6f\x74\145\162"); goto QVHjQ; pF5Dh: if (!empty($data["\145\162\x72\x6f\x72"])) { goto fsJHu; } goto MGEfU; ry9Wx: NKbsH: goto Owqly; hthaO: die; goto AQi4L; bA44i: $mp8go = false; goto otT6f; wWOde: zNNHP: goto PKvjB; oIAE9: $data["\145\162\x72\157\x72"] = $M_YRx["\x65\162\162\x6f\162"]; goto tp2Me; fXTLL: StTmw: goto NSF3Y; iLU_Q: JJqsH: goto bYxCK; JGZJm: Xh0m8: goto Ss6pf; H2x6X: $M_YRx = (array) @json_decode($Cpiw1); goto KL7t0; SCmO8: curl_setopt($z9JgZ, CURLOPT_RETURNTRANSFER, 1); goto TlGrc; jmPSq: $this->response->redirect($this->url->link(self::LINK, $this->token, "\123\123\114")); goto fOHi8; x0s8t: goto aGIYP; goto ry9Wx; XHdVY: if (!(!empty($data["\x6c\x69\x63\145\x6e\x73\145\137\x69\x6e\x66\157"]["\x77\145\142\x73\x69\x74\x65"]) && strpos($_SERVER["\110\124\x54\x50\137\110\x4f\x53\x54"], $data["\x6c\x69\x63\145\156\x73\145\x5f\x69\156\146\157"]["\x77\x65\142\163\x69\164\x65"]) !== false)) { goto v5VXr; } goto c6SyB; imG3F: hSw6K: goto jmPSq; zmXyh: sJqH6: goto u8lxy; Owqly: $Wy0d3 = 1; goto e_T_g; EsHWq: fsJHu: goto BGpcI; frHiO: $data["\143\157\154\165\x6d\x6e\137\x6c\145\146\x74"] = $this->load->controller("\x63\157\155\x6d\x6f\156\57\143\x6f\154\165\155\156\137\x6c\x65\146\164"); goto BUV62; peZwj: msLwd: goto g1OmK; JnJRz: if (!(!$mp8go || isset($this->request->get["\162\145\146\x72\145\163\150"]))) { goto Xh0m8; } goto UTZ41; Tktvl: goto WaDVC; goto HWj91; tp2Me: WaDVC: goto R6pkt; yNxiU: $data["\145\162\x72\157\162"] = "\x4c\x69\143\x65\156\163\x65\40\x6e\x75\x6d\x62\x65\x72\x20\x66\157\x72\x6d\x61\164\40\151\x73\x20\151\x6e\x63\157\162\x72\x65\x63\x74"; goto OKEPI; Dkal0: curl_setopt($z9JgZ, CURLOPT_BINARYTRANSFER, true); goto A4f_w; QVHjQ: if (version_compare(VERSION, 4, "\x3e\75")) { goto ZUw1_; } goto xlb1G; Ob4ey: curl_close($z9JgZ); goto H2x6X; lTibQ: $this->response->setOutput($this->load->view("\164\x6f\x6f\154\57\x67\153\x64\x5f\154\x69\x63\x65\156\x73\x65", $data)); goto tsHPw; NxDW4: $this->template = "\164\x6f\x6f\154\57\147\153\x64\137\154\151\143\x65\x6e\x73\145\x2e\164\160\x6c"; goto Y2ip6; fOHi8: z8p_W: goto RpLVC; TlGrc: curl_setopt($z9JgZ, CURLOPT_SSL_VERIFYPEER, 0); goto t0jZR; u8lxy: $data["\150\x65\x61\x64\145\x72"] = $this->load->controller("\x63\157\x6d\x6d\157\x6e\57\x68\x65\141\144\145\x72"); goto frHiO; W9O5y: $mp8go = isset($this->request->get["\162\x65\x66\162\x65\163\x68"]) ? 1 : rand(1, 12) == 2; goto vRceA; JvUeH: goto sKWVG; goto ugQJ2; wig5K: if (version_compare(VERSION, "\x32", "\x3e\75")) { goto sJqH6; } goto WQ00i; grxUj: if (!$mp8go) { goto msLwd; } goto ZR7YW; g1OmK: if (!($this->request->server["\122\x45\121\125\x45\123\124\x5f\115\105\124\110\x4f\104"] == "\120\x4f\x53\124" && isset($this->request->post["\154\x69\143\137\x6e\x75\x6d\142\145\x72"]))) { goto f1Ckh; } goto kD8OF; otT6f: if (in_array($_SERVER["\122\x45\x4d\x4f\x54\x45\x5f\x41\104\x44\x52"], array("\x31\x32\67\56\60\x2e\60\56\61", "\72\72\61", "\61\71\x32\x2e\61\66\70\x2e\60\x2e\x31")) || !$this->user->hasPermission("\x6d\x6f\x64\x69\146\171", self::$LINK)) { goto NKbsH; } goto ne10O; sEW2r: $this->response->setOutput($this->load->view("\164\157\157\154\x2f\x67\153\144\x5f\154\x69\x63\x65\x6e\163\145\x2e\x74\160\x6c", $data)); goto v7fM5; ne10O: if ($sDvMz) { goto kCCw5; } goto x0s8t; pbejP: goto JJqsH; goto zmXyh; y0Ge3: $this->model_setting_setting->deleteSetting(md5(HTTP_SERVER . self::MODULE)); goto oIAE9; P6cpl: sKWVG: goto iLU_Q; ahhud: $sDvMz = $this->config->get(md5(HTTP_SERVER . self::MODULE)); goto bA44i; WEwMY: if (version_compare(VERSION, "\x32", "\76\75")) { goto hSw6K; } goto ZUHkM; MGEfU: $z9JgZ = curl_init(); goto RicnI; Ozol2: if ($mp8go) { goto M1PSK; } goto CClwY; R6pkt: goto vJjOA; goto wWOde; BGpcI: f1Ckh: goto uslt2; DScjT: $this->config->set("\164\x65\x6d\160\x6c\141\164\x65\x5f\145\156\x67\x69\x6e\x65", "\164\145\155\x70\x6c\x61\x74\x65"); goto lTibQ; vRceA: aGIYP: goto Taqnj; xkLM6: $this->data =& $data; goto NxDW4; tYa6i: $data["\x6c\x69\143\145\156\163\145\x5f\151\x6e\x66\x6f"] = json_decode(base64_decode($sDvMz), 1); goto XHdVY; RicnI: curl_setopt($z9JgZ, CURLOPT_URL, "\x68\164\x74\x70\163\72\57\57\147\x65\145\153\157\144\145\x76\x2e\143\x6f\x6d\57\x6c\x69\x63\x65\156\x73\x65\x2e\x70\150\160"); goto NF0sK; vaALZ: goto z8p_W; goto imG3F; AQi4L: QDqYA: goto wig5K; c2zDh: kCCw5: goto tYa6i; R0QNh: if (isset($M_YRx["\145\162\x72\157\x72"])) { goto h0wsN; } goto Ozol2; yaZqM: $this->response->redirect($this->url->link(self::$LINK, $this->token, "\123\x53\x4c")); goto XSgis; v7fM5: goto n624J; goto rR44w; ZR7YW: $this->request->server["\x52\105\x51\x55\x45\123\x54\x5f\115\x45\124\110\x4f\x44"] = "\x50\x4f\123\x54"; goto q3mXM; SDUK9: v5VXr: goto W9O5y; OKEPI: itW6K: goto pF5Dh; CZYvf: bM7hS: goto yaZqM; cVgmm: $Cpiw1 = curl_exec($z9JgZ); goto Ob4ey; Taqnj: if (!(empty($Wy0d3) || $mp8go)) { goto KuVBd; } goto grxUj; KL7t0: if (!empty($M_YRx["\x73\x75\143\x63\x65\163\x73"])) { goto zNNHP; } goto R0QNh; ugQJ2: ZUw1_: goto WXj4H; WQ00i: $data["\x63\x6f\154\x75\x6d\x6e\x5f\154\x65\146\164"] = ''; goto xkLM6; WXj4H: $this->response->setOutput($this->load->view("\145\x78\164\145\x6e\x73\151\157\156\57" . self::MODULE . "\x2f\164\x6f\157\x6c\x2f\x67\153\144\x5f\x6c\151\143\145\156\163\145", $data)); goto P6cpl; NF0sK: curl_setopt($z9JgZ, CURLOPT_REFERER, "\x68\164\164\160\72\57\x2f{$_SERVER["\110\124\x54\x50\137\110\x4f\123\124"]}{$_SERVER["\122\105\x51\125\105\x53\124\137\x55\x52\x49"]}"); goto SCmO8; iwNr8: curl_setopt($z9JgZ, CURLOPT_USERAGENT, "\115\x6f\172\x69\x6c\x6c\x61\x2f\65\x2e\60\40\50\x57\x69\156\144\157\167\163\x20\116\x54\40\61\60\56\60\x3b\40\x57\117\127\66\x34\51\40\101\160\x70\154\x65\x57\x65\x62\x4b\151\x74\x2f\65\x33\67\56\x33\x36\x20\x28\113\110\124\115\x4c\x2c\40\154\x69\x6b\x65\40\x47\x65\x63\x6b\x6f\51\40\x43\x68\x72\157\x6d\145\x2f\x35\x31\56\60\x2e\62\67\60\64\x2e\61\60\x33\x20\x53\x61\146\141\x72\x69\57\x35\63\67\x2e\63\66"); goto Dkal0; UTZ41: $this->session->data["\163\165\x63\x63\x65\x73\163"] = $M_YRx["\x73\x75\143\x63\x65\163\x73"]; goto ZYRX8; ZYRX8: if (!empty(self::$LINK)) { goto bM7hS; } goto WEwMY; XSgis: HVcvU: goto JGZJm; CClwY: $data["\x65\162\162\x6f\162"] = "\x45\162\x72\x6f\162\x20\x64\x75\x72\x69\156\147\40\141\x63\x74\151\x76\141\x74\x69\x6f\156\x20\x70\162\157\143\145\x73\x73\x2c\40\160\154\x65\x61\163\x65\40\x63\157\x6e\x74\141\x63\164\x20\163\165\x70\x70\x6f\x72\x74"; goto xoWwu; xlb1G: if (version_compare(VERSION, 3, "\76\x3d")) { goto dIeod; } goto sEW2r; ax3DL: $this->model_setting_setting->editSetting(md5(HTTP_SERVER . self::MODULE), array(md5(HTTP_SERVER . self::MODULE) => $M_YRx["\x69\156\146\x6f"])); goto gy0UK; lxn9M: $this->load->model("\163\x65\x74\x74\151\x6e\x67\x2f\163\x65\164\164\x69\x6e\x67"); goto y0Ge3; XYe2v: if (!(version_compare(VERSION, 4, "\x3c") && !is_file(DIR_TEMPLATE . "\164\157\x6f\154\57\147\153\x64\137\154\151\x63\x65\156\163\145\x2e\164\160\154"))) { goto QDqYA; } goto hthaO; xoWwu: M1PSK: goto Tktvl; ZUHkM: $this->redirect($this->url->link(self::LINK, $this->token, "\123\x53\x4c")); goto vaALZ; A4f_w: curl_setopt($z9JgZ, CURLOPT_POSTFIELDS, http_build_query(array("\163\x6e" => $this->request->post["\x6c\151\x63\137\x6e\x75\155\x62\x65\162"], "\x74\x77" => !empty($this->request->post["\x6c\151\143\137\x74\145\x73\x74"]), "\151\160" => isset($_SERVER["\x53\105\122\x56\x45\122\137\101\x44\x44\122"]) ? $_SERVER["\123\x45\x52\126\105\122\137\101\x44\x44\x52"] : '', "\x6d\144" => self::MODULE, "\167\x73" => HTTP_SERVER, "\162\x66" => $mp8go))); goto cVgmm; tsHPw: n624J: goto JvUeH; q3mXM: $this->request->post = array("\154\151\x63\137\x6e\x75\155\142\145\162" => $data["\154\x69\143\x65\x6e\x73\145\137\151\x6e\146\x6f"]["\x6c\151\143\145\156\x73\145"]); goto peZwj; RpLVC: goto HVcvU; goto CZYvf; PKvjB: $this->load->model("\x73\x65\164\164\151\x6e\x67\x2f\163\145\x74\x74\x69\x6e\x67"); goto ax3DL; t0jZR: curl_setopt($z9JgZ, CURLOPT_POST, 1); goto iwNr8; bYxCK: return 0; goto fXTLL; Y2ip6: $this->children = array("\x63\x6f\155\155\157\x6e\57\150\145\x61\x64\145\162", "\143\157\155\155\157\x6e\57\x66\x6f\x6f\x74\x65\x72"); goto F7leC; F7leC: $this->response->setOutput($this->render()); goto pbejP; rR44w: dIeod: goto DScjT; uslt2: if (!empty($M_YRx["\163\165\x63\x63\x65\163\x73"])) { goto StTmw; } goto XYe2v; HWj91: h0wsN: goto lxn9M; c6SyB: $Wy0d3 = 1; goto SDUK9; kD8OF: if (!(!$this->request->post["\154\151\143\137\156\165\155\142\145\162"] || strlen(trim($this->request->post["\x6c\x69\143\137\156\x75\x6d\142\145\162"])) != 17)) { goto itW6K; } goto yNxiU; Ss6pf: vJjOA: goto EsHWq; NSF3Y: KuVBd:                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           
      
      if (isset($this->session->data['success'])) {
        $data['success'] = $this->session->data['success'];
        unset($this->session->data['success']);
      } else $data['success'] = '';
      
      if (isset($this->session->data['error'])) {
        $data['error'] = $this->session->data['error'];
        unset($this->session->data['error']);
      } else $data['error'] = '';
      
        $this->document->setTitle($this->language->get('title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
          $this->model_setting_setting->editSetting('webp_image', $this->request->post);

          if (version_compare(VERSION, '3', '>=')) {
            $new_post = array();
            
            foreach ($this->request->post as $k => $v) {
              $new_post['module_'.$k] = $v;
            }
            
            $this->model_setting_setting->editSetting('module_webp_image', $new_post);
          }

          $this->session->data['success'] = $this->language->get('text_success');

          if (version_compare(VERSION, '2', '>=')) {
            $this->response->redirect($this->url->link(self::LINK, $this->token . $redirect_store, 'SSL'));
          } else {
            $this->redirect($this->url->link(self::LINK, $this->token . $redirect_store, 'SSL'));
          }
            
        }

        if (!function_exists('imagewebp')) {
          $this->error['warning'] = 'imagewebp() function is not available, try to contact your host to ask if they can enable webp support for GD, or try to change php version to higher version';
        }
        
        if (isset($this->error['warning'])) {
          $data['warning'] = $this->error['warning'];
        } else {
          $data['warning'] = '';
        }
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_header'] = $this->language->get('entry_header');
        $data['entry_content_top'] = $this->language->get('entry_content_top');
        $data['entry_content_bottom'] = $this->language->get('entry_content_bottom');
        $data['entry_footer'] = $this->language->get('entry_footer');
        $data['entry_quality'] = $this->language->get('entry_quality');
        $data['text_clear_webp_cache'] = $this->language->get('text_clear_webp_cache');

        $data['token'] = $this->token;


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->extension_link
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('title'),
            'href' => $this->url->link('module/webp_image', $this->token, true)
        );

        $data['action'] = $this->url->link('module/webp_image', $this->token, true);

        $data['cancel'] = $this->extension_link;

        if (isset($this->request->post['webp_image_status'])) {
          $data['webp_image_status'] = $this->request->post['webp_image_status'];
        } else {
          $data['webp_image_status'] = $this->config->get('webp_image_status');
        }

        if (isset($this->request->post['webp_image_quality'])) {
          $data['webp_image_quality'] = $this->request->post['webp_image_quality'];
        } else {
          $data['webp_image_quality'] = $this->config->get('webp_image_quality');
        }
        
        $data['webp_image_quality'] = $data['webp_image_quality'] ? $data['webp_image_quality'] : 90;
        
        if (isset($this->request->post['webp_image_header'])) {
          $data['webp_image_header'] = $this->request->post['webp_image_header'];
        } else {
          $data['webp_image_header'] = $this->config->get('webp_image_header');
        }
        
        if (isset($this->request->post['webp_image_content_top'])) {
          $data['webp_image_content_top'] = $this->request->post['webp_image_content_top'];
        } else {
          $data['webp_image_content_top'] = $this->config->get('webp_image_content_top');
        }
        
        if (isset($this->request->post['webp_image_content_bottom'])) {
          $data['webp_image_content_bottom'] = $this->request->post['webp_image_content_bottom'];
        } else {
          $data['webp_image_content_bottom'] = $this->config->get('webp_image_content_bottom');
        }
        
        if (isset($this->request->post['webp_image_footer'])) {
          $data['webp_image_footer'] = $this->request->post['webp_image_footer'];
        } else {
          $data['webp_image_footer'] = $this->config->get('webp_image_footer');
        }
        
        $this->load->model('tool/image');
        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 80, 60);
		
        if (isset($this->request->post['webp_image_img'])) {
          $data['webp_image_img'] = $this->request->post['webp_image_img'];
        } else if ($this->config->get('webp_image_img')){
          $data['webp_image_img'] = $this->config->get('webp_image_img');
        } else {
          $data['webp_image_img'] = $this->config->get('config_logo');
        }
        
        if (!empty($data['webp_image_img']) && file_exists(DIR_IMAGE . $data['webp_image_img'])) {
          //$data['thumbnail'] = $this->model_tool_image->resize($this->request->post['webp_image_img'], 100, 100);
          $data['thumbnail'] = HTTP_CATALOG.'image/'.$data['webp_image_img'];
          $thumb_file = DIR_IMAGE . $data['webp_image_img'];
        } else {
          $data['thumbnail'] = HTTP_CATALOG.'image/'.$this->config->get('config_logo');
          $thumb_file = DIR_IMAGE . $this->config->get('config_logo');
        }
        
        $data['orig_size'] = $this->getSize($thumb_file);
        $data['orig_type'] = pathinfo($thumb_file, PATHINFO_EXTENSION);
        
        if(VERSION >= '2.0.0.0'){
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        if(VERSION < '2.2.0.0'){
            $additional_extension = '.tpl';
        } else {
            $additional_extension = '';
        }

        if (VERSION >= '3.0.2.0') {
            $this->config->set('template_engine', 'template');
        }
        $view = $this->load->view('module/webp_image'.$additional_extension, $data);
        $this->response->setOutput($view);
      } else {
          foreach ($data as $k => $v) {
              $this->data[$k] = $v;
          }
          $this->template = 'module/webp_image15.tpl';
          $this->children = array(
              'common/header',
              'common/footer'
          );
          $this->response->setOutput($this->render());
      }
    }

    private function getSize($file) {
      if (file_exists($file)) {
        $size = filesize($file);

        $suffix = array(
          'B',
          'KB',
          'MB',
        );

        $i = 0;

        while (($size / 1024) > 1) {
          $size = $size / 1024;
          $i++;
        }
        
        return round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i];
      }
    }
    
    public function test() {
      $orig = $this->request->post['image'];
      $quality = (int) $this->request->post['quality'];
      
      if (!file_exists(DIR_IMAGE.$orig)) {
        die('Original image not found');
      }
      
      $this->config->set('webp_image_quality', $quality);
      
      $this->load->model('tool/webp_image');
      $webp = urldecode($this->model_tool_webp_image->convert($orig, true));
      
      $origSize = filesize(DIR_IMAGE.$orig);
      
      if (!file_exists(DIR_IMAGE.'../'.$webp)) {
        die('Converted webp image not found');
      }
      
      $newSize = filesize(DIR_IMAGE.'../'.$webp);
      
      $fileSize = $this->getSize(DIR_IMAGE.'../'.$webp);
      
      $percentageDifference = round((($origSize - $newSize) / $newSize) * 100);
      
      $percentageDifference = round((1 - $origSize / $newSize) * 100);
      
      if ($webp) {
        echo '<div class="img-thumbnail" style="margin-bottom:5px"><img class="img-responsive" src="'.HTTP_CATALOG.$webp.'?q='.$quality.'" alt=""></div>';
        echo '<div>
        <b>WEBP Image</b><br/>
        <i class="fa fa-file-image-o"></i> '. $fileSize . '<br/>
        <i class="fa fa-magic"></i> Quality: '. $quality . '%<br/><br/>';
        if ($percentageDifference > 0) {
          echo '<span style="color:#e15231;font-weight:bold"><i class="fa fa-exclamation-triangle"></i> '. $percentageDifference . '% heavier</span><br/>';
        } else {
          echo '<span style="color:#84ac6e;font-weight:bold"><i class="fa fa-leaf"></i> '. abs($percentageDifference) . '% lighter</span><br/>';
        }
        echo '</div>';
      } else {
        echo '<div class="alert alert-info"><i class="fa fa-exclamation-triangle"></i> No test image, please select an image and save settings</div>';
      }
      
      exit;
    }
    
  public function process() {
    if (!$this->user->hasPermission('modify', self::LINK)) {
      $this->load->language(self::LINK);
      die($this->language->get('text_demo_mode'));
    }
    
    //sleep(1);
    ini_set('memory_limit', -1);
    $start_time = time();
    
    if (!empty($this->request->get['start'])) {
      $this->load->model('tool/webp_image');
      $pagesToCache = $this->model_tool_webp_image->getPageList();
      
      $total_items = count($pagesToCache);
      
      $init = ($this->request->get['start'] == 'init') ? true : false;
      
      if ($init) {
        $count = 0;
        $this->removeWebp(DIR_IMAGE.'cache', $count);
      }
      
      $start = (int) $this->request->get['start'];
      
      if (defined('GKD_CRON')) {
        $limit = 9999999999;
      } else {
        $limit = 30;
      }
      
      if ($init) {}
      
      $items_processed = 0;
      
      foreach ($pagesToCache as $i => $url) {
        if ($i < $start) {
          continue;
        }
        
        if (defined('GKD_CRON') && (time() - 10) > $start_time) {
          break;
        }
        
        if ($items_processed > $limit) {
          break;
        }
        
        @file_get_contents($url);
        
        $items_processed++;
      }
      
      $processed = $start + $items_processed;
      
      if ($processed > $total_items) {
        $processed = $total_items;
      }
      
      if ($total_items == 0 || !$items_processed) {
        $progress = 100;
      } else {
        $progress = floor(($processed / $total_items) * 100);
      }
      
      header('Content-type: application/json');
      echo json_encode(array(
        'success'=> 1,
        'processed' => $processed,
        'progress' => $progress,
        'finished' => (($processed >= $total_items) || !$items_processed),
      ));
      
    }
	}
  
  private function removeWebp($dir, &$count){
    $elements = glob($dir.'/*');
    
    foreach ($elements as $element) {
      if (is_dir($element)) {
        $this->removeWebp($element, $count);
      }
      
      if (is_file($element)) {
        if (substr($element, -4) == 'webp') {
          unlink($element);
          $count++;
        }
      }
    }
  }

  public function clearWebpCache() {
    $json = [];
    $count = 0;
    $this->removeWebp(DIR_IMAGE.'cache', $count);
    $json['count'] = $count;
    $json['success'] = 'Success! Webp cache was cleared!';
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', self::LINK)) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
  }
}