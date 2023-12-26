How to set up and start:
Change directory to app root (scraper) and use ./start.sh

Functionality improvements needed:

1) The URL strings must start with http:// or https:// because the scraper will
default to base url https://localhost/append-the-given-url-here.

It will also fail if the website administrator has not redirected http to https by
default and accepts only https (but we try to access via http).

Solutions:
Input sanitization for urls can be achieved through middleware. To be completed later.
Request routing cannot be really fixed. An error logging mechanism can be implemented
for better visibility and decision making. TODO 


It will also fail if the website admin has not set cname records for to resolve to 
website domain. For example www.website.com === website.com === http://www.website.com
2) The results are not grouped by some logic which would be
very useful. In the scenario where we want to scrape an e-commerce
to extract for example all sold laptops, their names and prices, the
currently returned results are dumped in bulk for each selector
Real example:
- given input:
```
{
  "name": "merry christmas",
  "urls": [
    {
      "url": "https://toscrape.com/",
      "selectors": [
        {"name": "title", "selector": "tr th"},
        {"name": "table-values", "selector": "tr td"}
      ]
    }
  ]
}

```

- returned results look like this:
```
  "title" => array:2 [
    0 => "Details"
    1 => "Endpoints"
  ]
  "table-values" => array:24 [
    0 => "Amount of items"
    1 => "1000"
    2 => "Pagination"
    3 => "✔"
    4 => "Items per page"
    5 => "max 20"
    6 => "Requires JavaScript"
    7 => "✘"
    8 => "Default"
    9 => "Microdata and pagination"
    10 => "Scroll"
    11 => "infinite scrolling pagination"
    12 => "JavaScript"
    13 => "JavaScript generated content"
    14 => "Delayed"
    15 => "Same as JavaScript but with a delay (?delay=10000)"
    16 => "Tableful"
    17 => "a table based messed-up layout"
    18 => "Login"
    19 => "login with CSRF token (any user/passwd works)"
    20 => "ViewState"
    21 => "an AJAX based filter form with ViewStates"
    22 => "Random"
    23 => "a single random quote"
  ]
```
Solutions:
To come up with a better solution the use-case scenarios need to be looked into more in depth. TODO