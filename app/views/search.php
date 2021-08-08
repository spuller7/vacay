<div id="searchPage" class="background">
    <div class="container" style="margin: auto;">

        <div class="row">
            <div class="autocomplete" style="display:inline-flex; margin-left:auto;">
                <input id="commemorateInput" type="text" style="width:20vw;" class="form-control" name="" value='' placeholder='Search For Your Favorite Spots'>

                <div></div>

                <button style="white-space: nowrap;">Commemorate Adventure</button>
            </div>
        </div>

        <div class="row">
            <div class="card" style="width: 100%">

                <div class="card-body">
                    <div id="search" class="container">
                        <form id="search-examples-form">
                            <div class="row" style="align-items: flex-end;">
                                <div class="col-9">
                                    <label for="filter1">City</label>
                                    <input type="text" class="form-control" name="filter1" value='Ann Arbor, Michigan' disabled>
                                </div>
                                <div class="col-3">
                                    <button id="exploreButton" class="btn btn-labeled" style="height: calc(1.5em + .75rem + 2px); background: linear-gradient(135deg,#ff690f 0%,#e8381b 100%); color: #ffffff; width: 100%;">Explore</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="filters">Filters</div>
                                </div>
                            </div>
                            <div id="filters">
                                <div class="row">
                                    <div class="col-12">
                                        <label>Categories</label>
                                        <div style="display: block;">
                                            <button class="btn btn-primary disabled">Breakfast</button>
                                            <button class="btn btn-primary disabled">Lunch</button>
                                            <button class="btn btn-primary disabled">Dinner</button>
                                            <button class="btn btn-primary disabled">Coffee & Tea</button>
                                            <button class="btn btn-primary disabled">Sweets</button>
                                            <button class="btn btn-primary disabled">21+</button>
                                        </div>
                                    </div>
                                </div>          
                            <div class="row">
                                <div class="col-12">
                                    <label>Price</label>
                                    <div style="display: block;">
                                        <button id="freeCheckbox" class="btn btn-primary disabled">Free</button>
                                        <button id="oneDollarCheckbox" class="btn btn-primary disabled">$</button>
                                        <button id="twoDollarCheckbox" class="btn btn-primary disabled">$$</button>
                                        <button id="threeDollarCheckbox" class="btn btn-primary disabled">$$$</button>
                                    </div>
                                </div>
                            </div>
</div>
                        </form>
                    </div>

                    <div id="loader" class="container" style="display: none;">
                        <div class="row" style="margin:15px;">
                            <div class="col-12">
                                <h5 style="text-align: center;">Finding the best adventure...</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="loader">
                            <div class="bar">
                                <div class="loaded"></div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div id="result" class="container" style="display: none;">
                        <div class="row">
                            <div class="col-12">
                                <h1>{{location['title']}}</h1>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="infoHeader">Address</div>
                                <p>{{location['address']}}</p>
                            </div>

                            <div class="col-6">
                                <div class="infoHeader">Hours</div>
                                <p>{{location['hours']}}</p>
                            </div>
                        </div>
                    </div>

                    <div id="specialResult" class="container" style="display: none;">
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
            'phone': ''
        },
    },
    methods: {
        getAdventure: function() {
            let data = {};
            let controller = this;

            showLoader();

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

                            $('#search').hide();
                            $('#result').show();
                        }
                        else
                        {
                            adventure = res.response.result;
                            searchController.location['title'] = adventure['name'];
                            searchController.location['hours'] = adventure['opening_hours']['open_now'];
                            searchController.location['address'] = adventure['formatted_address'];
                            searchController.location['phone'] = adventure['formatted_phone_number'];

                            $key = 'AIzaSyA3tAENcwKmOa6m2Y4B4SIXbEEi_GN0F4A';
                            searchController.location['photo'] = "https://maps.googleapis.com/maps/api/place/photo?photoreference=" + adventure['photos'][0]['photo_reference'] + "&sensor=false&maxheight=500&maxwidth=500&key=" + $key;

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
                        if (!res.response.candidates) { return false;}

                        let suggestions = res.response.candidates;
                        console.log(suggestions);
                        if (suggestions.length <= 0) { return false;}

                        currentFocus = -1;
                        /*create a DIV element that will contain the items (values):*/
                        a = document.createElement("DIV");
                        a.setAttribute("class", "autocomplete-items");
                        /*append the DIV element as a child of the autocomplete container:*/
                        document.getElementById('commemorateInput').parentNode.appendChild(a);

                        for (i = 0; i < suggestions.length; i++) {
                                /*create a DIV element for each matching element:*/
                                b = document.createElement("DIV");
                                /*make the matching letters bold:*/
                                b.innerHTML = "<p>" + suggestions[i].name + ' - ' + suggestions[i].formatted_address + "</strong>";
                                //b.innerHTML += arr[i].substr(val.length);
                                /*insert a input field that will hold the current array item's value:*/
                                console.log(b);
                                b.innerHTML += "<input type='hidden' value='" + suggestions[i].name + "'>";
                                /*execute a function when someone clicks on the item value (DIV element):*/
                                b.addEventListener("click", function(e) {
                                    /*insert the value for the autocomplete text field:*/
                                    $('#commemorateInput').value = this.getElementsByTagName("input")[0].value;
                                    /*close the list of autocompleted values,
                                    (or any other open lists of autocompleted values:*/
                                    closeAllLists();
                                });
                                a.appendChild(b);
                        }
                    }
                    else
                    {
                        console.log("Unexpected Error");
                    }
                }

            });
        }
    }
});

// Jquery event handler functions
$(function() {
    $('#search-examples-button').on('click', function() {
        $('#search-examples-form').submit();
    });

    $('#commemorateInput').on('input', function() {
        if (this.value.length > 2)
        {
            searchController.getPlaceSuggestions(this.value);
        }
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

    
  /*execute a function when someone clicks in the document:*/
  document.addEventListener("click", function (e) {
      closeAllLists(e.target);
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
        $('#result').show();
    }
    }
    var runloader = setInterval(loader,50); 

    function rnd(m,n) {
    m = parseInt(m);
    n = parseInt(n);
    return Math.floor( Math.random() * (n - m + 1) ) + m;
    }
}

</script>