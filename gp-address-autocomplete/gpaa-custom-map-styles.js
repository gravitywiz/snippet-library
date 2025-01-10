/**
 * Gravity Perks // GP Address Autocomplete // Custom Map Styles
 *
 * Apply custom styles to a Map Field.
 *
 * http://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */

window.gform.addFilter('gpaa_map_options', function(opts, formId, fieldId) {
	/**
	 * Array of google.map.MapTypeStyle objects
	 * @reference https://developers.google.com/maps/documentation/javascript/reference/map#MapTypeStyle
	 *
	 * You can customize this as you would like for custom map styles. For some pre-made styles, check out: https://snazzymaps.com/
	 * For instance, the default styles in this snippet come from this Snazzy Map style: https://snazzymaps.com/style/93/lost-in-the-desert
	 */
	opts.styles = [
    	{
    	    elementType: 'labels',
    	    stylers: [
    	        {
    	            'visibility': 'off'
    	        },
    	        {
    	            'color': '#f49f53'
    	        }
    	    ]
    	},
    	{
    	    featureType: 'landscape',
    	    stylers: [
    	        {
    	            'color': '#f9ddc5'
    	        },
    	        {
    	            'lightness': -7
    	        }
    	    ]
    	},
    	{
    	    featureType: 'road',
    	    stylers: [
    	        {
    	            'color': '#813033'
    	        },
    	        {
    	            'lightness': 43
    	        }
    	    ]
    	},
    	{
    	    featureType: 'poi.business',
    	    stylers: [
    	        {
    	            'color': '#645c20'
    	        },
    	        {
    	            'lightness': 38
    	        }
    	    ]
    	},
    	{
    	    featureType: 'water',
    	    stylers: [
    	        {
    	            'color': '#1994bf'
    	        },
    	        {
    	            'saturation': -69
    	        },
    	        {
    	            'gamma': 0.99
    	        },
    	        {
    	            'lightness': 43
    	        }
    	    ]
    	},
    	{
    	    featureType: 'road.local',
    	    elementType: 'geometry.fill',
    	    stylers: [
    	        {
    	            'color': '#f19f53'
    	        },
    	        {
    	            'weight': 1.3
    	        },
    	        {
    	            'visibility': 'on'
    	        },
    	        {
    	            'lightness': 16
    	        }
    	    ]
    	},
    	{
    	    featureType: 'poi.park',
    	    stylers: [
    	        {
    	            'color': '#645c20'
    	        },
    	        {
    	            'lightness': 39
    	        }
    	    ]
    	},
    	{
    	    featureType: 'poi.school',
    	    stylers: [
    	        {
    	            'color': '#a95521'
    	        },
    	        {
    	            'lightness': 35
    	        }
    	    ]
    	},
    	{
    	    featureType: 'poi.medical',
    	    elementType: 'geometry.fill',
    	    stylers: [
    	        {
    	            'color': '#813033'
    	        },
    	        {
    	            'lightness': 38
    	        },
    	        {
    	            'visibility': 'off'
    	        }
    	    ]
    	},
    	{
    	    featureType: 'poi.sports_complex',
    	    stylers: [
    	        {
    	            'color': '#9e5916'
    	        },
    	        {
    	            'lightness': 32
    	        }
    	    ]
    	},
    	{
    	    featureType: 'poi.government',
    	    stylers: [
    	        {
    	            'color': '#9e5916'
    	        },
    	        {
    	            'lightness': 46
    	        }
    	    ]
    	},
    	{
    	    featureType: 'transit.station',
    	    stylers: [
    	        {
    	            'visibility': 'off'
    	        }
    	    ]
    	},
    	{
    	    featureType: 'transit.line',
    	    stylers: [
    	        {
    	            'color': '#813033'
    	        },
    	        {
    	            'lightness': 22
    	        }
    	    ]
    	},
    	{
    	    featureType: 'transit',
    	    stylers: [
    	        {
    	            'lightness': 38
    	        }
    	    ]
    	},
    	{
    	    featureType: 'road.local',
    	    elementType: 'geometry.stroke',
    	    stylers: [
    	        {
    	            'color': '#f19f53'
    	        },
    	        {
    	            'lightness': -10
    	        }
    	    ]
    	},
	];

	return opts;
});
