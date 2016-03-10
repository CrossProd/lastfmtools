var animationSpeed = 300;

var MAIN_SERVER = "http://lastfm.gammalyrae.com/";

function UpdatePreview()
{
    if (username == null)
    {
        return;
    }
    
    var style = $("#styleSelectBox").val();
    var period = $("#periodSelectBox").val();
    var cols  = $("#columnsSelectBox").val();
    var rows  = $("#rowsSelectBox").val();

    var border = $('#borderColorSelectBox').val();

    var font  = $("#typewriterFontSelectBox").val();
    var fontCase  = $("#typewriterCapitalizationSelectBox").val();
    var background  = $("#typewriterBackgroundSelectBox").val();
    var mask  = $("#typewriterColorSelectBox").val();
    var distortion  = $("#typewriterDistortionSelectBox").val();

    var spiralColor  = $("#spiralColorSelectBox").val();

    $("#previewImage").hide();
    $("#previewLoadingImage").show();

    var url = null;

    switch (selectedSubMenu)
    {
        case "albumCollage":
            url = style == 0 ? 
                        "collage/topalbums_var01/" + username + "," + period  + "," + border + ".jpg" :
                        "collage/topalbums/" + username + "," + period  + "," + cols + "," + rows + "," + border + ".jpg";
            break;
        case "artistCollage":
            url = style == 0 ? 
                        "collage/topartists_var01/" + username + "," + period  + "," + border + ".jpg" :
                        "collage/topartists/" + username + "," + period  + "," + cols + "," + rows + "," + border + ".jpg";
            break;
        case "artistTypewriter":     
            url = "typewriter/topartists/" + username + "," + period + "," + font + "," + fontCase + "," + background + "," + mask + "," + distortion + ".png";               
            break;
        case "artistSpiral":     
            url = "spiral/topartists/" + username + "," + period  + "," + spiralColor + ".png";
            break;
    }

    var imageUrl   = MAIN_SERVER + "output/" + url;
    var previewUrl = MAIN_SERVER + "output/preview/" + url;

    $("#previewImage").attr('src', previewUrl);

    var bbcode =
        "[url=http://lastfm.gammalyrae.com/][img]" + imageUrl + "[/img][/url]";

    $("#bbcodeTextArea").val(bbcode);     
    
}

function UpdateSettingsForm(animate)
{
    animationSpeed = animate ? 300 : 0;

    var selectedStyle  = $("#styleSelectBox").val();

    $('#styleControlGroup').hide(animationSpeed);

    if (selectedSubMenu == "artistSpiral")
    {
        $('#spiralColorControlGroup').show(animationSpeed);
    }
    else
    {
        $('#spiralColorControlGroup').hide(animationSpeed);
    }

    if (selectedSubMenu == "artistTypewriter")
    {
        $("#typewriterFontControlGroup").show(animationSpeed);
        $("#typewriterCapitalizationControlGroup").show(animationSpeed);
        $("#typewriterBackgroundControlGroup").show(animationSpeed);
        $("#typewriterDistortionControlGroup").show(animationSpeed);
        $("#typewriterColorControlGroup").show(animationSpeed);
    }
    else
    {
        $("#typewriterFontControlGroup").hide(animationSpeed);                    
        $("#typewriterCapitalizationControlGroup").hide(animationSpeed);
        $("#typewriterBackgroundControlGroup").hide(animationSpeed);
        $("#typewriterDistortionControlGroup").hide(animationSpeed);
        $("#typewriterColorControlGroup").hide(animationSpeed);
    }

    if (selectedSubMenu == "artistCollage" || selectedSubMenu == "albumCollage")
    {
        $('#borderColorControlGroup').show(animationSpeed);
        $('#styleControlGroup').show(animationSpeed);

        if (selectedStyle == 0) // fixed
        {
            $('#columnsControlGroup').hide(animationSpeed);
            $('#rowsControlGroup').hide(animationSpeed);
        }
        else // customizable
        {
            $('#columnsControlGroup').show(animationSpeed);;
            $('#rowsControlGroup').show(animationSpeed);

            var selectedNrOfColumns  = $("#columnsSelectBox").val();
            var selectedNrOfRows = $("#rowsSelectBox").val();
    
            var maxRows =  Math.floor(50 / selectedNrOfColumns);

            if (selectedNrOfRows == null || (selectedNrOfRows > maxRows))
            {
                selectedNrOfRows = 3;
            }

            var options = "";

            for (var i = 1; i <= maxRows; i++) 
            {
                if (i == selectedNrOfRows)
                {               
                    options += "<option value=\"" + i + "\" selected>" + i + "</option>";
                }
                else
                {
                    options += "<option value=\"" + i + "\">" + i + "</option>";
                }
            }

            $("#rowsSelectBox").html(options);
        }
    }
    else
    {
        $('#borderColorControlGroup').hide(animationSpeed);
        $('#columnsControlGroup').hide(animationSpeed);
        $('#rowsControlGroup').hide(animationSpeed);
    }               
}

function UpdateBody()
{
    $('#welcomeBody').hide();
    $('#priorityBody').hide();
    $('#aboutFAQBody').hide();
    $('#aboutTechnologyBody').hide();

    switch (selectedSubMenu)
    {
        case "homeWelcome":
            $('#welcomeBody').show();
            break;
        case "priority":
            $('#priorityBody').show();
            break;
        case "aboutTechnology":
            $('#aboutTechnologyBody').show();
            break;
        case "aboutFAQ":
            $('#aboutFAQBody').show();
            break;
    }
}

function UpdateSubMenu()
{
    $('#homeWelcomeMenuItem').attr('class', '');
    $('#priorityMenuItem').attr('class', '');
    $('#aboutTechnologyMenuItem').attr('class', '');
    $('#aboutFAQMenuItem').attr('class', '');
    $('#albumCollageMenuItem').attr('class', '');
    $('#artistCollageMenuItem').attr('class', '');
    $('#artistSpiralMenuItem').attr('class', '');
    $('#artistTypewriterMenuItem').attr('class', '');

    switch (selectedSubMenu)
    {
        case "homeWelcome":
            $('#homeWelcomeMenuItem').attr('class', 'active');
            break;
        case "priority":
            $('#priorityMenuItem').attr('class', 'active');
            break;
        case "aboutTechnology":
            $('#aboutTechnologyMenuItem').attr('class', 'active');
            break;
        case "aboutFAQ":
            $('#aboutFAQMenuItem').attr('class', 'active');
            break;
        case "albumCollage":
            $('#albumCollageMenuItem').attr('class', 'active');
            break;
        case "artistCollage":
            $('#artistCollageMenuItem').attr('class', 'active');
            break;
        case "artistSpiral":
            $('#artistSpiralMenuItem').attr('class', 'active');
            break;
        case "artistTypewriter":
            $('#artistTypewriterMenuItem').attr('class', 'active');
            break;
    }
}

//
// preview image loaded event
//

$('#previewImage').load(function() {

    $("#previewLoadingImage").hide();
    $("#previewImage").show();
});

//
// twitter events

$('#twitterTweetButton').click(function(event) 
{
    var collagePeriod  = $("#collagePeriodSelectBox2").val();

    $("#twitterTweetWaitingImage").show();
    $('#twitterTweetButton').prop('disabled', true);

    $.get('twittertweet.php?collagePeriod=' + collagePeriod, function(data) 
    {
        alert(data);

        $("#twitterTweetWaitingImage").hide();
        $('#twitterTweetButton').prop('disabled', false);
    });

    return false;

});

$('#twitterUpdatePreferencesButton').click(function(event) 
{
    var uploadPeriod  = $("#uploadPeriodSelectBox").val();
    var collagePeriod  = $("#collagePeriodSelectBox").val();

    $('#twitterUpdatePreferencesButton').prop('disabled', true);

    $.get('twitterupdatepreferences.php?uploadPeriod=' + uploadPeriod + '&collagePeriod=' + collagePeriod, function(data) 
    {
        alert(data);

        $('#twitterUpdatePreferencesButton').prop('disabled', false);
    });

    return false;
});

$('#twitterSignOutButton').click(function(event) 
{
    window.location = "twitterclearsession.php";

    return false;
});

$('#twitterSignInButton').click(function(event) 
{
    window.location = "twitterlogin.php";

    return false;
});

//
// widgets submenu click events
//

$('#albumCollageMenuLink').click(function(event) {

    selectedSubMenu = "albumCollage";

    UpdateSubMenu();
    UpdateSettingsForm(false);
    UpdatePreview();

    return false;

});

$('#artistCollageMenuLink').click(function() {

    selectedSubMenu = "artistCollage";

    UpdateSubMenu();
    UpdateSettingsForm(false);
    UpdatePreview();

    return false;
    
});

$('#artistSpiralMenuLink').click(function() {

    selectedSubMenu = "artistSpiral";

    UpdateSubMenu();
    UpdateSettingsForm(false);
    UpdatePreview();

    return false;
    
});

$('#artistTypewriterMenuLink').click(function() {

    selectedSubMenu = "artistTypewriter";

    UpdateSubMenu();
    UpdateSettingsForm(false);
    UpdatePreview();

    return false;
    
}); 

//
// home submenu click events
//

$('#homeWelcomeMenuLink').click(function() {

    selectedSubMenu = "homeWelcome";

    UpdateSubMenu();
    UpdateBody();

    return false;
    
});

$('#priorityMenuLink').click(function() {

    selectedSubMenu = "priority";

    UpdateSubMenu();
    UpdateBody();

    return false;
    
}); 

$('#aboutTechnologyMenuLink').click(function() {

    selectedSubMenu = "aboutTechnology";

    UpdateSubMenu();
    UpdateBody();

    return false;
    
}); 

$('#aboutFAQMenuLink').click(function() {

    selectedSubMenu = "aboutFAQ";

    UpdateSubMenu();
    UpdateBody();

    return false;
    
}); 

//
// widgets settings changes
//

$('#styleSelectBox').on('change', function(event) 
{
    UpdateSettingsForm(true);
    UpdatePreview();
});

$('#columnsSelectBox').on('change', function(event) 
{
    UpdateSettingsForm(true);
    UpdatePreview();
});

$('#spiralColorSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

$('#typewriterDistortionSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

$('#typewriterBackgroundSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

$('#typewriterColorSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

$('#typewriterFontSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

$('#typewriterCapitalizationSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

$('#borderColorSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

$('#rowsSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

$('#periodSelectBox').on('change', function(event) 
{
    UpdatePreview();
});

//
// bbcode box events
//
$("#bbcodeTextArea").click(function(e) {

    $("#bbcodeTextArea").focus();
    $("#bbcodeTextArea").select();

});

//
// page ready event
//

$(document).ready(function() {

    UpdateBody();

    if (page == 'twitter')
    {
        $("#uploadPeriodSelectBox").val(twitterSelectedTweetPeriod);
        $("#collagePeriodSelectBox").val(twitterSelectedCollagePeriod);
    }
    else if (page == 'widgets')
    {
        UpdateSettingsForm(false);
        UpdatePreview();        
    }

});