<?php
    use app\models\HereCategory;
    $categories = HereCategory::findAll(['active' => 1]);
?>

<div id="searchPage">
    <div class="container">

        <div class="row" style="margin-top: 25px;">
            <div class="card" style="width: 100%">
                <div class="card-body">
                    <div class="container">
                        <div class="row" style="margin-bottom: 0px;">
                            <div class="col-4">
                                <div class="title">Jaunt</div>
                            </div>
                            <div class="col-8" style="display: flex;">
                                <div class="autocomplete" style="display:inline-flex; margin-left:auto;align-items: center;">
                                    <input id="suggestionInput" type="text" style="width: 20vw; margin-left: auto;" class="form-control" name="" value='' data-place_id='' placeholder='Search For Your Favorite Spots'>
                                    <button id="recommendButton" class="btn btn-primary" style="white-space: nowrap;" disabled>Recommend</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card" style="width: 100%">

                <div class="card-body">
                    <div id="search" class="container">
                        <form id="search-examples-form">
                            <div class="row" style="align-items: flex-end;">
                                <div class="col-9">
                                    <label for="city">City</label>
                                    <div class="autocomplete" style="display:flex; margin-left:auto;align-items: center;">
                                        <input type="text" class="form-control" id="cityInput" name="city" value='Ann Arbor, Michigan'>
                                        <input type="hidden" name="cityPlaceID" value='ChIJMx9D1A2wPIgR4rXIhkb5Cds'>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <button id="exploreButton" class="btn btn-labeled" style="height: calc(1.5em + .75rem + 2px); background: linear-gradient(135deg,#ff690f 0%,#e8381b 100%); color: #ffffff; width: 100%;">Explore</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div id="filterHeader">Filters <i :class="show_filters ? 'fas fa-chevron-down' : 'fas fa-chevron-right'"></i></div>
                                </div>
                            </div>
                            <div id="filters" :style="show_filters ? '' : 'display: none'">
                                <div class="row">
                                    <div class="col-12">
                                        <label>Categories</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <select multiple name="categories[]" size="1">
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id']; ?>" class="btn btn-primary" selected><?= $category['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>   
                            <div class="row">
                                <div class="col-12">
                                    <label>Price</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <select multiple name="prices[]" size="1">
                                        <option value="0" class="btn btn-primary" selected>Free</option>
                                        <option value="1" class="btn btn-primary" selected>$</option>
                                        <option value="2" class="btn btn-primary" selected>$$</option>
                                        <option value="3" class="btn btn-primary" selected>$$$</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                                            </div>

        <div class="row">
            <div class="card" style="width: 100%">

                <div class="card-body">
                    <div id="result" class="container">
                        <div class="row">
                            <div class="col-12">
                                <h1>{{location['title']}}</h1>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <img :src="location['photo']" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="infoHeader">Address</div>
                                <p>{{location['address']}}</p>
                            </div>

                            <div class="col-6">
                                <div class="infoHeader">Hours</div>
                                <p style="color: green; font-weight: bold;">{{location['hours'] ? 'Open' : 'Closed'}}</p>
                            </div>

                            <div class="col-6">
                                <div class="infoHeader">Phone Number</div>
                                <a style="color: blue">{{location['phone']}}</a>
                            </div>

                            <div class="col-6">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>

/**
 * Page Dynamic Controller
 * 
 * Using view object so that whenever the items property is updated,
 * the table will automatically be updated without the need to listen
 * for any data changes. However, this is just a preference, can be done
 * with only javascript if desired
 */
var searchController = new Vue({
    el: '#searchPage',
    data: {
        location: {
            'title': 'test title',
            'address': 'test address',
            'hours': 'hours',
            'photo': '',
            'phone': '',
        },
        suggested_place_id : '',
        show_filters : false
    },
    methods: {
        getAdventure: function() {
            let data = {};
            let controller = this;

            //showLoader();

            data['free'] = $('#freeCheckbox').hasClass('disabled') ? 0 : 1;
            data['oneDollar'] = $('#oneDollarCheckbox').hasClass('disabled') ? 0 : 1;
            data['twoDollar'] = $('#twoDollarCheckbox').hasClass('disabled') ? 0 : 1;
            data['threeDollar'] = $('#threeDollarCheckbox').hasClass('disabled') ? 0 : 1;

            $.ajax({
                // url directed to a the getExamplesTable function in the datatable.php in /contollers
                url: "/search/discover",
                type: 'GET',
                data: data,
                success: function(res)
                {
                    if (res.success)
                    {
                        if (!res.specialPlace)
                        {
                            let numResults = res.response.items.length;

                            let choiceIndex = Math.floor(Math.random() * numResults);
                            let adventure = res.response.items[choiceIndex];
                            searchController.location['title'] = adventure['title'];
                            searchController.location['hours'] = adventure['openingHours'] ? adventure['openingHours'][0]['text'][0] : 'N/A';
                            searchController.location['address'] = adventure['address']['label'];

                            //$('#search').hide();
                            //$('#result').show();
                        }
                        else
                        {
                            adventure = res.response.result;
                            searchController.location['title'] = adventure['name'];
                            searchController.location['hours'] = adventure['opening_hours']['open_now'];
                            searchController.location['address'] = adventure['formatted_address'];
                            searchController.location['phone'] = adventure['formatted_phone_number'];

                            $key = 'AIzaSyA3tAENcwKmOa6m2Y4B4SIXbEEi_GN0F4A';
                            searchController.location['photo'] = "https://maps.googleapis.com/maps/api/place/photo?photoreference=" + adventure['photos'][1]['photo_reference'] + "&sensor=false&maxheight=500&maxwidth=500&key=" + $key;

                            //$('#search').hide();
                            //$('#specialResult').show();
                        }
                        
                    }
                    else
                    {
                        console.log("Unexpected Error");
                    }
                }

            });
        },

        getPlaceSuggestions: function(input)
        {
            let data = {};
            data['query'] = input;

            $.ajax({
                // url directed to a the getExamplesTable function in the datatable.php in /contollers
                url: "/search/search_suggestions",
                type: 'GET',
                data: data,
                success: function(res)
                {
                    if (res.success)
                    {
                        let a, b, i;
                        let val = res.query;
                        closeAllLists();
                        if (!res.response) { return false;}

                        let suggestions = res.response;
                        console.log(suggestions);
                        if (suggestions.length <= 0) { return false;}

                        currentFocus = -1;
                        /*create a DIV element that will contain the items (values):*/
                        a = document.createElement("DIV");
                        a.setAttribute("class", "autocomplete-items");
                        /*append the DIV element as a child of the autocomplete container:*/
                        document.getElementById('suggestionInput').parentNode.appendChild(a);

                        for (i = 0; i < Math.min(suggestions.length, 5); i++) {
                                /*create a DIV element for each matching element:*/
                                b = document.createElement("div");
                                b.dataset.name = suggestions[i].name;
                                b.dataset.place_id = suggestions[i].place_id;
                                b.classList.add('suggestion')
                                
                                /*make the matching letters bold:*/
                                b.innerHTML = "<p>" + suggestions[i].name + ' - ' + suggestions[i].formatted_address + "</p>";
                                a.appendChild(b);

                                b.addEventListener("click", function(e) {
                                    $('#suggestionInput').val(this.dataset.name);
                                    $('#recommendButton').prop('disabled', false);
                                    searchController.suggested_place_id = this.dataset.place_id;
                                    closeAllLists();
                                });
                        }
                    }
                    else
                    {
                        console.log("Unexpected Error");
                    }
                }

            });
        },

        submitRecommendation: function()
        {
            let data = {};
            data['place_id'] = searchController.suggested_place_id;

            $.ajax({
                // url directed to a the getExamplesTable function in the datatable.php in /contollers
                url: "/search/recommend_place",
                type: 'POST',
                data: data,
                success: function(res)
                {
                    if (res.success)
                    {
                        
                    }
                    else
                    {
                        console.log("Unexpected Error");
                    }
                }

            });
        },

        getCitySuggestions: function(input)
        {
            let data = {};
            data['query'] = input;

            $.ajax({
                // url directed to a the getExamplesTable function in the datatable.php in /contollers
                url: "/search/search_cities",
                type: 'GET',
                data: data,
                success: function(res)
                {
                    if (res.success)
                    {
                        let a, b, i;
                        let val = res.query;
                        closeAllLists();
                        if (!res.response) { return false;}

                        let suggestions = res.response;
                        if (suggestions.length <= 0) { return false;}

                        currentFocus = -1;
                        /*create a DIV element that will contain the items (values):*/
                        a = document.createElement("DIV");
                        a.setAttribute("class", "autocomplete-items");
                        /*append the DIV element as a child of the autocomplete container:*/
                        document.getElementById('cityInput').parentNode.appendChild(a);

                        for (i = 0; i < suggestions.length; i++) {
                                /*create a DIV element for each matching element:*/
                                b = document.createElement("div");
                                b.dataset.description = suggestions[i].description;
                                b.dataset.place_id = suggestions[i].place_id;
                                b.classList.add('suggestion')
                                
                                /*make the matching letters bold:*/
                                b.innerHTML = "<p>" + suggestions[i].description + "</p>";
                                a.appendChild(b);

                                b.addEventListener("click", function(e) {
                                    $('#cityInput').val(this.dataset.description);
                                    $('#cityInput').attr('value', this.dataset.place_id);
                                    
                                    closeAllLists();
                                });
                        }
                    }
                    else
                    {
                        console.log("Unexpected Error");
                    }
                }

            });
        },
    }
});

// Jquery event handler functions
$(function() {

    $('#search-examples-button').on('click', function() {
        $('#search-examples-form').submit();
    });

    $('#suggestionInput').on('input', function() {
        if (this.value.length > 2)
        {
            searchController.getPlaceSuggestions(this.value);
        }
    });

    $('#cityInput').on('input', function() {
        if (this.value.length > 2)
        {
            searchController.getCitySuggestions(this.value);
        }
    });

    $('#recommendButton').on('click', function(e) {
        searchController.submitRecommendation();
    });

    $('#exploreButton').on('click', function(e) {
        e.preventDefault();
        searchController.getAdventure();
    });

    $('#filters .btn').on('click', function(e)
    {
        e.preventDefault();
        target = $(this);
        if(target.hasClass('disabled'))
        {
            target.removeClass('disabled');
        }
        else
        {
            target.addClass('disabled');
        }
    });

    $('#filterHeader').on('click', function(e) {
        searchController.show_filters = !searchController.show_filters;
    });

    
  /*execute a function when someone clicks in the document:*/
  document.addEventListener("click", function (e) {
      //closeAllLists(e.target);
  });
});

function closeAllLists(elmnt) {
    let x = $(".autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i]) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }

function showLoader()
{
    $('#search').hide();
    $('#loader').show();
    //okpt("Galaxy Progress Loader");
    for(var i = 0; i < 40; i++) {
        var radius = (rnd(1600,3400)/10);
        var modifier = radius/160;
        $(".loader").append("<div class=\"spinner\" style=\"animation: bar " + 4*modifier + "s linear infinite; height: " + radius + "px; animation-delay: -" + (rnd(40,80)/10) + "s\"></div>");
    }

    var loaded = 0;
    function loader() {
    if(rnd(0,1) == 1) {
        loaded++;
        $(".spinner:nth-child(" + Math.floor(loaded/2.5) + ")").css("height", "0px");
        $(".loaded").css("width", (loaded + "%"));
    }
    if(loaded >= 100) {
        clearInterval(runloader);
        $('#loader').hide();
        $('#specialResult').show();
    }
    }
    var runloader = setInterval(loader,10); 

    function rnd(m,n) {
    m = parseInt(m);
    n = parseInt(n);
    return Math.floor( Math.random() * (n - m + 1) ) + m;
    }
}

</script>