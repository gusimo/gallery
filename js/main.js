const apiUrl = "http://localhost/gallery";

function opendirectory(path){
    $(".selectFolder").off();
    $(".vote").off();
    $(".mapslink").off();
    showLoading();

    $.ajax({
        dataType: "json",
        url: apiUrl + '/rest/getcontent.php?dir=' + path,
        data: null,
        success: function(data,status){
            console.log(data);
            console.log(status);
            renderFileList(data);
            window.location.hash = data.path;
        }
    });
}

function appendVotes(context){
    var result = {
        average: 0,
        votes:0,
        stars:
            [
                {
                    full:false,
                    half:false
                },
                {
                    full:false,
                    half:false
                },
                {
                    full:false,
                    half:false
                },
                {
                    full:false,
                    half:false
                },
                {
                    full:false,
                    half:false
                }
            ]
    }
    if(context.ratingsum && context.ratingcount){
        var rating = 0;
        if (Number(context.ratingcount) > 0) {
            rating = (Number(context.ratingsum) / Number(context.ratingcount));
        }
        result.average = rating;
        result.votes = context.ratingcount;

        for(var i=0; i<5; i++){
            var currentStarValue = rating - i;
            if(currentStarValue >= 1){
                result.stars[i].full = true;
            }
            else if(currentStarValue >= 0.5){
                result.stars[i].half = true;
            }
        }
    }

    context.votes = result;
    return result;
};

function renderFileList(data){
    //prepare data
    for(var i in data.files){
        appendVotes(data.files[i]);
    }
console.log("new data", data);
    var source   = document.getElementById("filesAndFolders").innerHTML;
    var template = Handlebars.compile(source);
    var html    = template(data);
    //console.log(html);
    $("#content").html(html);

    $(".selectFolder").click(function(event){
        event.preventDefault();
        console.log(event);
        var path = $(event.currentTarget).attr('data-id');
        opendirectory(path);
    });

    $(".vote").click(function(event){
       alert('vote');
    });

    $(".mapslink").click(function(event){

        var url = $(event.currentTarget).attr('data-href');
        var win = window.open(url, '_blank');
        win.focus();
        event.preventDefault();
    });

    $("#justified").justifiedGallery({
        rowHeight : 230,
        maxRowHeight : 300,
        cssAnimation : true,
        lastRow : 'nojustify',
        margins : 3
    });
}

function showLoading(){
    var source   = document.getElementById("throbberTemplate").innerHTML;
    var template = Handlebars.compile(source);
    var html    = template({});
    $("#content").html(html);
}

$(document).ready(function(){
   var startdir = '/';
   if (window.location.hash && window.location.hash.length > 0){
       startdir = window.location.hash.replace("#","");
       console.log(startdir);
   }
    opendirectory(startdir);

   Handlebars.registerHelper('mapsurl', function(address){
       return "https://maps.google.com/maps?q=" + encodeURIComponent(address);
   });

   Handlebars.registerHelper('linkStyling',function(something){
      return "text-decoration:underline;color:white;cursor:pointer;";
   });

   function getStars(context){
       if(context.ratingsum && context.ratingcount) {
           var result = "Rating: ";
           var rating = 0;
           if (Number(context.ratingcount) > 0) {
               rating = (Number(context.ratingsum) / Number(context.ratingcount));
           }
           for (var i = 0; i < 5; i++) {
               result += '<a href="#" class="vote" data-hash="' + context.hash + '" data-value="1">';
               if (i < rating) {
                   result += '<i class="fa fa-star"></i>';
               }
               else {
                   if (i + 0.3 < rating) {
                       result += '<i class="fas fa-star-half"></i>';
                   }
                   else {
                       result += '<i class="far fa-star"></i>';
                   }

               }
               result += "</a>";
               return result;
           }
       }
       else{
           return "";
       }
   }

    Handlebars.registerHelper('exif', function(context, options) {
        var result = "";

        if(context.views){
            result += 'Views: ' + context.views + "&nbsp;";
        }

        if(context.ratingsum && context.ratingcount){
            result += getStars(context);
        }

        if(context.exif) {
            if(context.exif.camera){
                result += "<br/>Kamera: " + context.exif.camera + "&nbsp;";
            }
            if(context.exif.place){
                result += 'Ort: <div class="mapslink" data-href="https://maps.google.com/maps?q='+encodeURIComponent(context.exif.place.formatted)+'">' + context.exif.place.formatted + "</div>"+ "&nbsp;";
            }
        }

        return result;
    });

});