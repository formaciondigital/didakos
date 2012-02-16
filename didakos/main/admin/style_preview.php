<?php
$language_file = array('admin', 'create_course', 'messages', 'courses');
include_once('../inc/global.inc.php');
if (isset($_GET['style']) AND $_GET['style']<>'')
{
	$style=Security::remove_XSS($_GET['style']);
	//$htmlHeadXtra[] = '<link href="../css/'.$_GET['style'].'/default.css" rel="stylesheet" type="text/css">';
	echo '<link href="../css/'.$style.'/default.css" rel="stylesheet" type="text/css">';
}
else
{
	$currentstyle = api_get_setting('stylesheets');
	echo '<link href="../css/'.$currentstyle.'/default.css" rel="stylesheet" type="text/css">';
}


//Display::display_header($tool_name);
include(api_get_path(INCLUDE_PATH).'banner.inc.php');

?>
<!-- start of #main wrapper for #content and #menu divs -->
  <!--   Begin Of script Output   -->
  <div class="maincontent">
    <h3><?php echo get_lang('DokeosConfigSettings') ?></h3>
    <div id="courseintro">
      <p><?php echo get_lang('langIntroductionText') ?>
    </div>
    <div id="courseintro_icons">
    <a href="#"><img src="../img/edit.gif" alt="edit"/></a><a href="#"><img src="../img/delete.gif" alt="delete"/></a></div>
    <div class="normal-message"><?php echo get_lang('Messages') ?></div>
    <div class="error-message"><?php echo get_lang('DeleteError') ?></div>
    <table width="750">
      <tr>
        <td>
        <table>
            <tr>
              <td width="220">
              
              <table id="smallcalendar">
                  <tr id="title">
                    <td width="10%"><a href="#"><<</a></td>
                    <td width="80%" colspan="5" align="center"> 2006</td>
                    <td width="10%"><a href="#">>></a></td>
                  </tr>
                  <tr>
                    <td class="weekdays"><?php echo get_lang('MondayShort') ?></td>
                    <td class="weekdays"><?php echo get_lang('TuesdayShort') ?></td>
                    <td class="weekdays"><?php echo get_lang('WednesdayShort') ?></td>
                    <td class="weekdays"><?php echo get_lang('ThursdayShort') ?></td>
                    <td class="weekdays"><?php echo get_lang('FridayShort') ?></td>
                    <td class="weekdays"><?php echo get_lang('SaturdayShort') ?></td>
                    <td class="weekdays"><?php echo get_lang('SundayShort') ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="days_weekend">1</td>
                  </tr>
                  <tr>
                    <td class="days_week">2</td>
                    <td class="days_week">3</td>
                    <td class="days_week">4</td>
                    <td class="days_week">5</td>
                    <td class="days_week">6</td>
                    <td class="days_weekend">7</td>
                    <td class="days_weekend">8</td>
                  </tr>
                  <tr>
                    <td class="days_week">9</td>
                    <td class="days_week">10</td>
                    <td class="days_week">11</td>
                    <td class="days_week">12</td>
                    <td class="days_week">13</td>
                    <td class="days_weekend">14</td>
                    <td class="days_weekend">15</td>
                  </tr>
                  <tr>
                    <td class="days_week">16</td>
                    <td class="days_week">17</td>
                    <td class="days_week">18</td>
                    <td class="days_week">19</td>
                    <td class="days_week">20</td>
                    <td class="days_weekend">21</td>
                    <td class="days_weekend">22</td>
                  </tr>
                  <tr>
                    <td class="days_week">23</td>
                    <td class="days_today">24</td>
                    <td class="days_week">25</td>
                    <td class="days_week">26</td>
                    <td class="days_week">27</td>
                    <td class="days_weekend">28</td>
                    <td class="days_weekend">29</td>
                  </tr>
                  <tr>
                    <td class="days_week">30</td>
                    <td class="days_week">31</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
        <td width="500">
          <table width="100%">
            <tr>
              <td></td>
              <td align="right"></td>
            </tr>
          </table>
          <table class="data_table" width="100%">
            <tr>
              <th style="width:100px"><a href="#"><?php echo get_lang('FirstName') ?></a>&nbsp;&#8595; </th>
              <th style="width:100px"><a href="#"><?php echo get_lang('LastName') ?></a></th>
            </tr>
            <tr class="row_even">
              <td ><?php echo get_lang('FirstName') ?></td>
              <td ><?php echo get_lang('LastName') ?></td>
            </tr>
            <tr class="row_odd">
              <td >Patrick</td>
              <td >Cool</td>
            </tr>
            <tr class="row_even">
              <td >Patrick</td>
              <td >Cool</td>
            </tr>
            <tr class="row_odd">
              <td >Patrick</td>
              <td >Cool</td>
            </tr>
          </table>
          <table width="100%">
            <tr>
              <td></td>
              <td align="right"></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
  <div class="menu" style="width:200px">
    <form action="#" method="post" id="loginform" name="loginform">
      <label><?php echo get_lang('UserName') ?></label>
      <input type="text" name="login" id="login" size="15" value="" />
      <label><?php echo get_lang('Password') ?></label>
      <input type="password" name="password" id="password" size="15" />
      <input type="submit" value="<?php echo get_lang('langOk') ?>" name="submitAuth" class="submitauth"/>
    </form>
    <div class="menusection"><span class="menusectioncaption"><?php echo get_lang('User') ?></span>
      <ul class="menulist">
        <li><a href="#"><?php echo get_lang('CourseManagement') ?></a></li>
        <li><a href="#"><?php echo get_lang('CreateCourseCategory') ?></a></li>
      </ul>
    </div>
    <div class="note"><b><?php echo get_lang('ExampleForum') ?></b><br />
      <?php echo get_lang('ExampleThreadContent') ?>.</div>
  </div>
<?php
Display::display_footer();
?>
