<!DOCTYPE html>
<?php
$script = $_SERVER['PHP_SELF'];
?>
<html lang="en" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="robots" content="noindex, nofollow"/>
  <title>WikiTree - Family Tree and Free Genealogy - wikitree-javascript-sdk -
    Example </title>
  <link rel="stylesheet" href="https://www.wikitree.com/css/main-new.css?2"
        type="text/css"/>
  <script defer src="fontawesome/js/all.js"></script>
  <script
    src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script
    src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
  <script src="scripts/wikitree.js"></script>
  <script type="text/javascript">

      // In the ready() function we run some code when the DOM is ready to go.
      $(document).ready(function () {
          wikitree.init({});
          wikitree.session.checkLogin({})
              .then(function (data) {
                  if (wikitree.session.loggedIn) {
                      /* We're already logged in and have a valid session. */
                      $('#need_login').hide();
                      $('#logged_in').show();
                      document.getElementById('request-id').addEventListener("keypress", function(e) {
                          if (e.key === 'Enter') {
                              walk(document.getElementById('request-id').value)
                          }
                      })
                  } else {
                      /* We're not yet logged in, but maybe we've been returned-to with an auth-code */
                      var x = window.location.href.split('?');
                      var queryParams = new URLSearchParams(x[1]);
                      if (queryParams.has('authcode')) {
                          var authcode = queryParams.get('authcode');
                          wikitree.session.clientLogin({'authcode': authcode})
                              .then(function (data) {
                                  if (wikitree.session.loggedIn) {
                                      /* If the auth-code was good, redirect back to ourselves without the authcode in the URL (don't want it bookmarked, etc). */
                                      window.location = '<?php echo $script ?>';
                                  } else {
                                      $('#need_login').show();
                                      $('#logged_in').hide();
                                  }
                              });
                      } else {
                          $('#need_login').show();
                          $('#logged_in').hide();
                      }
                  }
                  $('#login-name').text(wikitree.session.user_name);
              });
      });

      function copy_to_clipboard(tag) {
          var el = document.getElementById(tag);
          var range = document.createRange();
          range.selectNodeContents(el);
          var sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
          document.execCommand("copy");
          sel.removeAllRanges();
          document.getElementById('copy_review').innerHTML = el.innerHTML;
          setTimeout(function () {
              document.getElementById('copy_review').innerHTML = '';
          }, 1500);
      }

      const monthName = [
          'Jan', 'January',
          'Feb', 'February',
          'Mar', 'March',
          'Apr', 'April',
          'May', 'May',
          'Jun', 'June',
          'Jul', 'July',
          'Aug', 'August',
          'Sep', 'September',
          'Oct', 'October',
          'Nov', 'November',
          'Dec', 'December'
      ];

      // date in yyyy-mm-dd format
      // longmonth = 0 for short month names
      //           = 1 for long month names
      //           < 0 for just year
      function format_date(date, longmonth = 0) {
          var ret = '';

          if (date && date !== '0000-00-00') {
              var parts = date.split('-');
              ret = parts[0];
              if (longmonth >= 0) {
                  var mon = parts[1].valueOf();
                  if (mon > 0) {
                      ret = monthName[(mon - 1) * 2 + longmonth] + ' ' + ret;
                      if (parts[2].valueOf() > 0)
                          ret = parts[2] + ' ' + ret;
                  }
              }
          }

          return ret;
      }

      function person_string_format(person, fmt) {
          var ret = '';

          for (var c of fmt) {
              switch (c) {
                  case 'f':
                      ret += person.FirstName;
                      break;
                  case 'F':
                      ret += person.FirstName.toUpperCase();
                      break;
                  case 'm':
                      ret += person.MiddleName;
                      break;
                  case 'M':
                      ret += person.MiddleName.toUpperCase();
                      break;
                  case 'l':
                      ret += person.LastNameCurrent;
                      break;
                  case 'L':
                      ret += person.LastNameCurrent.toUpperCase();
                      break;
                  case 'b':
                      ret += person.LastNameAtBirth;
                      break;
                  case 'B':
                      ret += person.LastNameAtBirth.toUpperCase();
                      break;
                  case 'n':
                      ret += person.Name;
                      break;
                  case 'a':
                      ret += format_date(person.BirthDate);
                      break;
                  case 'A':
                      ret += format_date(person.BirthDate, 1);
                      break;
                  case 'y':
                      ret += format_date(person.BirthDate, -1);
                      break;
                  case 'Y':
                      ret += format_date(person.DeathDate, -1);
                      break;
                  case 'z':
                      ret += format_date(person.DeathDate);
                      break;
                  case 'Z':
                      ret += format_date(person.DeathDate, 1);
                      break;
                  default:
                      ret += c;
              }
          }
          return ret;
      }

      function format_copy_line(person, idx, fmt = 'f l') {
          var ret = '';

          ret += "<span class='SMALL' id='P" + person.Id + "-" + idx.toString() + "'>";
          ret += '[[' + person.Name + '|';
          ret += person_string_format(person, fmt);
          ret += "]]</span>";
          ret += " <i class='fal fa-clipboard' onclick='copy_to_clipboard(\"P" + person.Id + "-" + idx.toString() + "\");'></i>";

          return ret;
      }

      function wlinks(person) {
          var ret = '';
          var idx = 1;
          var isMale = person.Gender === 'Male';

          ret += "<div class='" + (isMale ? 'BLUE' : 'PINK') + "' style='border:1px solid;'>";
          ret += "<div style='float: left;'>";
          ret += "<span>";
          ret += "<span class='pseudolink' onClick='walk(" + person.Id + ");'>" + person_string_format(person, 'f l') + "</span><br/> ";
          ret += "<span class='SMALL'>" + person_string_format(person, '(y-Y)') + "</span>";
          ret += "</span>";
          ret += "</div>";
          ret += "<div style='float: left;'>";
          ret += "<span>";
          if (!isMale) {
              if (person.MiddleName) {
                  ret += format_copy_line(person, idx++, 'f m (b) l') + '<br/>';
                  ret += format_copy_line(person, idx++, 'f m b') + '<br/>';
              }
              ret += format_copy_line(person, idx++, 'f (b) l') + '<br/>';
              ret += format_copy_line(person, idx++, 'f b') + '<br/>';
          }
          if (person.MiddleName) {
              ret += format_copy_line(person, idx++, 'f m l') + '<br/>'
          }
          ret += format_copy_line(person, idx++, 'f l') + '<br/>';
          ret += format_copy_line(person, idx++, 'f') + '<br/>';
          ret += "</span>";
          ret += "</div>";
          ret += "<div style='clear: both;'></div>";
          ret += "</div>";

          return ret;
      }

      function walk_group(group, target) {
          var html = '';
          if (group) {
              for (id in group) {
                  var c = group[id];
                  /*if (html !== '') {
                      html += ' / ';
                  }*/
                  html += wlinks(c);
              }
          }
          $('#' + target).html(html);
      }

      // Given a user id, "walk" that user by retrieving their data and sticking it into the page.
      function walk(user_id) {

          // If we don't have a user_id, use the one from the cookie (the one for the logged-in user).
          if (!user_id) {
              user_id = Cookies.get('wikitree_wtb_UserID');
          }
          // Go get the person data.
          var p = new Person({user_id: user_id});
          p.load({fields: 'Id,Name,Gender,FirstName,MiddleName,LastNameAtBirth,LastNameCurrent,BirthDate,DeathDate,Father,Mother,Parents,Children,Siblings,Spouses'}).then(function (data) {
              // Raw JSON results dumped to display div
              $('#raw').hide();
              // $('#raw').html("<h2>Raw Results</h2>\n<pre>" + JSON.stringify(data, null, 4) + "</pre>");

              //HEAD: h2 id="walk_name"
              $('#walk_name').html(wlinks(p));

              //Father: span id="walk_father"
              if (p.Parents[p.Father]) {
                  var f = p.Parents[p.Father];
                  $('#walk_father').html(wlinks(f));
              } else {
                  $('#walk_father').html('');
              }

              //Mother: span id="walk_mother"
              if (p.Parents[p.Mother]) {
                  var m = p.Parents[p.Mother];
                  $('#walk_mother').html(wlinks(m));
              } else {
                  $('#walk_mother').html('');
              }

              //Spouses: span id="walk_spouses"
              walk_group(p.Spouses, 'walk_spouses');

              //Children: span id="walk_children"
              walk_group(p.Children, 'walk_children');

              //Siblings: span id="walk_siblings"
              walk_group(p.Siblings, 'walk_siblings');
          });
      }

      function walk_req() {
          walk(document.getElementById('request-id').value)
      }

      // Log the user out of apps.wikitree.com by deleting all the cookies
      function appsLogout() {
          wikitree.session.logout();
          document.location.href = 'http://apps.wikitree.com/<?php echo $script;?>';
      }

  </script>
  <style type="text/css">
    #output {
      margin-top: 20px;
      border: 1px solid black;
      background-color: #eeffee;
      padding: 5px;
      overflow: auto;
      /*min-height: 200px;
      width: 500px;
      float: left;*/
    }

    #raw {
      margin-top: 20px;
      margin-left: 20px;
      border: 1px solid black;
      background-color: #eeffee;
      padding: 5px;
      /*width: 400px;
      min-height: 200px;
      max-height: 500px;
      overflow: scroll;
      float: left;*/
    }

    #logged_in {
      display: none;
    }

    #need_login {
    }

    .element-main {
      border: 2px crimson double;
      padding: 1px;
      margin: 1px;
    }

    .element-group {
      width: 45%;
    }

    .left {
      float: left;
    }

    .center {
      margin-left: auto;
      margin-right: auto;
    }

    .right {
      float: right;
    }

    .clear {
      clear: both;
    }

    .clright {
      clear: right;
    }

    .inline {
      display: inline;
    }

    .BLUE {
      background-color: #eeeeff;
    }

    .PINK {
      background-color: #ffeeee;
    }

    .GREEN {
      background-color: #eeffee;
    }
  </style>
</head>

<body class="mediawiki ns-0 ltr page-Main_Page">
<?php include "/home/apps/www/header.htm"; ?>

<div id="HEADLINE">
  <h1><?php echo $script ?></h1>
</div>

<div id="CONTENT" class="MISC-PAGE">

  <!-- This div is shown if the user is logged in. -->
  <div id="logged_in">
    You are logged in to WikiTree as <span id="login-name"><i
        class="fas fa-cog fa-spin"></i></span>.
    <ul>
      <li>You can <span class="pseudolink" onClick="appsLogout();">logout</span>.
      </li>
      <li>You can select a family to display from WikiTree by entering the
        appropriate name id (eg Tudor-4).
      </li>
      <li>Copy any wikitext link using the clipboard icon (<i
          class="far fa-clipboard"></i>) next to the appropriate text.
      </li>
      <li>Or you can return to <a href="http://apps.wikitree.com/">Apps</a>.
      </li>
    </ul>
    <form name="request" id="request-form" action="#">
      <label for="request-id">Enter WikiTree name id: </label>
      <input type="text" name="request-id" id="request-id" value="Tudor-4"/>
      <input type="button" name="select" value="Select" onclick="walk_req();"/>
    </form>

    <!-- This div has the spans filled in by our walk() function. -->
    <div id="output">
      <span id="copy_review"></span><br/>
      <div class="element-group left">Father: <span id="walk_father"></span></div>
      <div class="element-group right">Mother: <span id="walk_mother"></span></div>
      <div class="clear">
        <div class="element-group center element-main"><span id="walk_name"></span></div>
      </div>
      <div class="element-group left clear">Siblings: <span id="walk_siblings"></span>
      </div>
      <div class="element-group right">Spouses: <span id="walk_spouses"></span></div>
      <br/>
      <div class="element-group right clright">Children: <span id="walk_children"></span>
      </div>
      <div class="clear"><span class="pseudolink"
                               onClick="walk()">Restart with your profile</span>
      </div>
    </div>

    <!-- This div will hold the raw JSON output from a walk() call. -->
    <div id="raw"></div>

    <div style="clear:both;"></div>
  </div>

  <!-- This div is shown if the user is not logged in. -->
  <div id="need_login">
    You are not currently logged in to apps.wikitree.com. In order to access
    your WikiTree ancestry, please sign in with your WikiTree.com credentials.
    <form action="https://api.wikitree.com/api.php" method="POST">
      <input type="hidden" name="action" value="clientLogin">
      <input type="hidden" name="returnURL"
             value="https://apps.wikitree.com/<?php echo $script; ?>">
      <input type="submit" class="button" value="Client Login">
    </form>
  </div>

  <div id="dbg"></div>
</div>

<?php include "/home/apps/www/footer.htm"; ?>
</body>
</html>