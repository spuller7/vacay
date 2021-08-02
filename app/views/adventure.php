<div id="searchPage" class="background">
    <div class="container" style="margin: auto;">
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
                            <div class="row">
                                <div class="col-12">
                                    <label>Categories</label>
                                    <div style="display: block;">
                                        <button class="btn btn-primary">Breakfast</button>
                                        <button class="btn btn-primary">Lunch</button>
                                        <button class="btn btn-primary">Dinner</button>
                                        <button class="btn btn-primary">Coffee & Tea</button>
                                        <button class="btn btn-primary">Sweets</button>
                                        <button class="btn btn-primary">21+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label>Price</label>
                                    <div style="display: block;">
                                        <button class="btn btn-primary">Free</button>
                                        <button class="btn btn-primary">$</button>
                                        <button class="btn btn-primary">$$</button>
                                        <button class="btn btn-primary">$$$</button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
            'hours': 'hours'
        }
    },
    methods: {
        getAdventure: function() {
            let data = {};
            let controller = this;

            $.ajax({
                // url directed to a the getExamplesTable function in the datatable.php in /contollers
                url: "http://vacay-env.eba-rmdtx3dh.us-east-2.elasticbeanstalk.com/adventure/discover",
                type: 'GET',
                data: data,
                success: function(res)
                {
                    if (res.success)
                    {
                        let numResults = res.response.items.length;

                        let choiceIndex = Math.floor(Math.random() * numResults);
                        console.log(choiceIndex);
                        let adventure = res.response.items[choiceIndex];
                        console.log(adventure);
                        searchController.location['title'] = adventure['title'];
                        searchController.location['hours'] = adventure['openingHours'] ? adventure['openingHours'][0]['text'][0] : 'N/A';
                        searchController.location['address'] = adventure['address']['label'];

                        $('#search').hide();
                        $('#result').show();
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

    $('#exploreButton').on('click', function(e) {
        e.preventDefault();
        searchController.getAdventure();
    });
});
</script>