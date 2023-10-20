# AttributesGraphQl

**AttributesGraphQl** supports rendering dropdown attribute's text values on frontend. Usually Magento sends only the Option ID.
 But on frontend, the text value of the options is needed in most cases. 

To get a dropdown attribute's text value make sure you add _text suffix to the original attribute code.
In addition to that you have to provide the original attribute code as well in the request. See example. 


##Documentation

* [WIKI](https://corratech.jira.com/wiki/spaces/HCAW/pages/1766359103/Corra+AttributesGraphQl+Documentation)


###Get products list
```graphql
{
  products (filter: {sku:{eq:"test"}}) {
    items {
      name
      product_label
      product_label_text
      color
      color_text
    }
  }
}
``` 

**Response:**
```json
{
  "data": {
    "products": {
      "items": [
        {
          "name": "test",
          "product_label_text": "New",
          "color": 5440,
          "color_text": "Red"
        }
      ]
    }
  }
}
```
