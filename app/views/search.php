<?php
    use app\models\HereCategory;
    use app\core\GoogleAPI;
    
    Css::loadAll(array('datatable_actions', 'modal', 'search'));
    $categories = HereCategory::findAll(['active' => 1]);
?>

<div id="searchPage">
    <div class="container">
        <div class="row" style="margin-top: 25px;">
        
            <div class="card search-card" style="width: 100%">
                <div class="card-body">
                    <div class="container">
                        <div class="row" style="margin-bottom: 0px;">
                            <div class="col-4">
                                <div class="title"><b>EAT</b>SEEKS</div>
                            </div>
                            <div class="col-8" style="display: flex;">
                                <div class="autocomplete" style="display:inline-flex; margin-left:auto;align-items: center;">
                                    <input id="suggestionInput" type="text" style="width: 20vw; margin-left: auto;" class="form-control" name="" value='' data-place_id='' placeholder='Search For Your Favorite Spots'>
                                    <button id="recommendButton" class="btn btn-dark" style="white-space: nowrap;" disabled>
                                        Recommend
                                        <div id="recommend-loader" class="button-svg" style="display:none">
                                            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                width="24px" height="30px" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">
                                                <rect x="0" y="10" width="4" height="10" fill="#333" opacity="0.2">
                                                <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0s" dur="0.6s" repeatCount="indefinite" />
                                                <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0s" dur="0.6s" repeatCount="indefinite" />
                                                <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0s" dur="0.6s" repeatCount="indefinite" />
                                                </rect>
                                                <rect x="8" y="10" width="4" height="10" fill="#333"  opacity="0.2">
                                                <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.15s" dur="0.6s" repeatCount="indefinite" />
                                                <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.15s" dur="0.6s" repeatCount="indefinite" />
                                                <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.15s" dur="0.6s" repeatCount="indefinite" />
                                                </rect>
                                                <rect x="16" y="10" width="4" height="10" fill="#333"  opacity="0.2">
                                                <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.3s" dur="0.6s" repeatCount="indefinite" />
                                                <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.3s" dur="0.6s" repeatCount="indefinite" />
                                                <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.3s" dur="0.6s" repeatCount="indefinite" />
                                                </rect>
                                            </svg>
                                        </div>
                                        <div id="recommend-checkmark" class="button-svg" style="display: none">
                                            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                viewBox="0 0 98.5 98.5" enable-background="new 0 0 98.5 98.5" xml:space="preserve">
                                            <path class="checkmark" fill="none" stroke-width="8" stroke-miterlimit="10" d="M81.7,17.8C73.5,9.3,62,4,49.2,4
                                                C24.3,4,4,24.3,4,49.2s20.3,45.2,45.2,45.2s45.2-20.3,45.2-45.2c0-8.6-2.4-16.6-6.5-23.4l0,0L45.6,68.2L24.7,47.3"/>
                                            </svg>
                                        </div>
                                    </button>
                                        
                                    
                                    <div class="autocomplete-items-loader hidden">
                                        <div class="loading-items"><img src = "../../public/img/content/loader.svg" alt="Loader"/></div>
                                    </div>
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
                                        <input type="hidden" id="cityPlaceID" name="cityPlaceID" value='ChIJMx9D1A2wPIgR4rXIhkb5Cds'>
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
                                        <select multiple name="categories[]" size="1" style="max-width: 100%;">
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

    <div class="section-label row">
        Our Top Pick
    </div>

        <div class="row">
            <div id="result" class="card" style="width: 100%; display: none;">

                <div id="result-loader" class="card-body">
                    <div class="centerContent">
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                            width="24px" height="30px" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">
                            <rect x="0" y="10" width="4" height="10" fill="#333" opacity="0.2">
                            <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0s" dur="0.6s" repeatCount="indefinite" />
                            <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0s" dur="0.6s" repeatCount="indefinite" />
                            <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0s" dur="0.6s" repeatCount="indefinite" />
                            </rect>
                            <rect x="8" y="10" width="4" height="10" fill="#333"  opacity="0.2">
                            <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.15s" dur="0.6s" repeatCount="indefinite" />
                            <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.15s" dur="0.6s" repeatCount="indefinite" />
                            <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.15s" dur="0.6s" repeatCount="indefinite" />
                            </rect>
                            <rect x="16" y="10" width="4" height="10" fill="#333"  opacity="0.2">
                            <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.3s" dur="0.6s" repeatCount="indefinite" />
                            <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.3s" dur="0.6s" repeatCount="indefinite" />
                            <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.3s" dur="0.6s" repeatCount="indefinite" />
                            </rect>
                        </svg>
                    </div>
                    <div class="centerContent">Counting the Votes</div>
                </div>

                <div id="result-content" class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="location-title">{{location['title']}}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div id="carousel" class="col-12">
                            </div>                        
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="infoHeader">Address</div>
                                <p>{{location['address']}}</p>
                            </div>

                            <div class="col-6">
                                <div class="infoHeader">Hours</div>
                                <p :class="[show_filters ? 'open' : 'closed']">{{location['hours'] ? 'Open' : 'Closed'}}</p>
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

        <div class="section-label row">
            Community Recommendations
        </div>

        <div class="row">
        
            <div id="additional-places">
                
            </div>
        </div>
    </div>
</div>

<script>

var searchController = new Vue({
    el: '#searchPage',
    data: {
        location: {
            'title': 'test title',
            'address': 'test address',
            'hours': 'hours',
            'photo': '',
            'photos' : [],
            'phone': '',
        },
        adventures: [],
        suggested_place_id : '',
        show_filters : false,
        waiting_for_place_suggestions : false,
    },
    methods: {
        getAdventure: function() {
            let data = {};
            let controller = this;

            data['cityPlaceID'] = $('#cityPlaceID').val();

            data['free'] = $('#freeCheckbox').hasClass('disabled') ? 0 : 1;
            data['oneDollar'] = $('#oneDollarCheckbox').hasClass('disabled') ? 0 : 1;
            data['twoDollar'] = $('#twoDollarCheckbox').hasClass('disabled') ? 0 : 1;
            data['threeDollar'] = $('#threeDollarCheckbox').hasClass('disabled') ? 0 : 1;

            $('#result-loader').show();
            $('#result-content').hide();
            $('#result').show();

            $.ajax({
                // url directed to a the getExamplesTable function in the datatable.php in /contollers
                url: "/search/discover",
                type: 'POST',
                data: $('#search-examples-form').serialize(),
                success: function(res)
                {
                    if (res.success)
                    {
                        searchController.adventures = res.adventures;
                        let adventure = res.adventures[0];
                        console.log(adventure);
                        searchController.location['title'] = adventure['name'];
                        searchController.location['hours'] = adventure['openingHours'] ? adventure['openingHours'][0]['text'][0] : false;
                        searchController.location['address'] = adventure['formatted_address'];
                        searchController.location['photo'] = adventure['photo'];
                        searchController.location['photos'] = adventure['photo_list'];
                        searchController.location['phone'] = adventure['formatted_phone_number'];
                        searchController.buildImageCarousel();
                        searchController.buildRecommendationCarousel();

                        $('#result-loader').hide();
                        $('#result-content').show();
                    }
                    else
                    {
                        console.log("Unexpected Error");
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {i
                    alert(xhr.status);
                    alert(thrownError);
                  }
            });
        },

        buildImageCarousel : function ()
        {
            let carousel = $('#carousel');

            let container = document.createElement('div');
            container.classList.add('owl-carousel', 'owl-theme');


            for (const photo of searchController.location.photos)
            {
                let item = document.createElement('div');
                item.classList.add('item');
                
                let img = document.createElement('img');
                img.setAttribute('src', photo);

                item.append(img);
                container.append(item);
            }

            carousel.html(container);

            let total = searchController.location['photos'].length;

            $('.owl-carousel').owlCarousel({
                items:5,
                margin:10,
                nav:true,
            });
        },

        buildRecommendationCarousel: function()
        {
            let carousel = $('#additional-places');

            let container = document.createElement('div');
            container.classList.add('owl-carousel', 'owl-theme');
            container.setAttribute('id', 'recommendation-carousel')

            for (const location of searchController.adventures)
            {
                // Don't include top result choice in additional recommendations
                if (location.place_id == searchController.adventures[0].place_id)
                {
                    continue;
                }

                let card = document.createElement('div');
                card.classList.add('additional-place', 'card');
                
                let body = document.createElement('div');
                body.classList.add('card-body');
                
                let img = document.createElement('img');
                img.setAttribute('src', location.photo_list[0]);

                body.append(img);

                let footer = document.createElement('div');
                footer.classList.add('card-footer');

                let title = document.createElement('div');
                console.log(location);
                title.innerText = location.name;

                footer.append(title);

                card.append(body);
                card.append(footer);

                container.append(card);
            }

            console.log(container) ;
            carousel.html(container);

            let total = searchController.adventures.length;

            $('#recommendation-carousel').owlCarousel({
                margin:25,
            });


        },

        showPlaceLoader: function ()
        {
            $('.autocomplete-items-loader').removeClass('hidden');
        },

        hidePlaceLoader: function ()
        {
            $('.autocomplete-items-loader').addClass('hidden');
        },

        showNoResults: function ()
        {
            a = document.createElement("DIV");
            a.setAttribute("class", "autocomplete-items");
            /*append the DIV element as a child of the autocomplete container:*/
            document.getElementById('suggestionInput').parentNode.appendChild(a);

            b = document.createElement("div");
            b.classList.add('suggestion')
            
            /*make the matching letters bold:*/
            b.innerHTML = "<p>No Results Found</p>";
            a.appendChild(b);
        },

        getPlaceSuggestions: function(input)
        {
            let data = {};
            data['cityPlaceID'] = $('#cityPlaceID').val();
            data['query'] = input;

            closeAllLists();
            this.showPlaceLoader();
            let _this = this;

            $.ajax({
                // url directed to a the getExamplesTable function in the datatable.php in /contollers
                url: "/search/search_suggestions",
                type: 'GET',
                data: data,
                success: function(res)
                {
                    _this.hidePlaceLoader();
                    if (res.success)
                    {
                        let a, b, i;
                        let val = res.query;
                        closeAllLists();
                        if (!res.response) { _this.showNoResults(); return false;}

                        let suggestions = res.response;
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
                },

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
                        $('#recommend-loader').hide();
                        let button = $('#recommendButton');
                        button.addClass('dissapear');
                        $('#recommend-checkmark').show();

                        setTimeout(function () {
                            $('#recommend-checkmark').hide();
                            button.removeClass('dissapear');

                            $('#suggestionInput').val('');
                            $('#recommendButton').prop('disabled', true);
                            searchController.suggested_place_id = '';
                        }, 2000);
                        
                    }
                    else
                    {
                        console.log("Unexpected Error");
                    }
                },

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
                                    $('#cityPlaceID').attr('value', this.dataset.place_id);
                                    
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

    // Used to delay callback if called multiple times
    function debounce(callback, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(function () { callback.apply(this, args); }, wait);
        };
    }

    $('#search-examples-button').on('click', function() {
        $('#search-examples-form').submit();
    });

    $('#suggestionInput').on('input', debounce( () => {
        let val = $('#suggestionInput').val();
        if (val.length > 2)
        {
            searchController.getPlaceSuggestions(val);
        }
    }, 500));

    $('#cityInput').on('input', function() {
        if (this.value.length > 2)
        {
            searchController.getCitySuggestions(this.value);
        }
    });

    $('#recommendButton').on('click', function(e) {
        if (!$(this).hasClass('dissapear'))
        {
            $(this).addClass('dissapear');
            $('#recommend-loader').show();
            searchController.submitRecommendation();
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

    $('#filterHeader').on('click', function(e) {
        searchController.show_filters = !searchController.show_filters;
    });

    
  /*execute a function when someone clicks in the document:*/
  $(document).click(function (event) {            
    closeAllLists();
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

</script>

