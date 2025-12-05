// import '../scss/components/_map_open_layers.scss'
// import Map from 'ol/Map';
// import OSM from 'ol/source/OSM';
// import TileLayer from 'ol/layer/Tile';
// import View from 'ol/View';
// import VectorSource from "ol/source/Vector";
// import VectorLayer from "ol/layer/Vector";
// import {Fill, Icon, Stroke, Style, Text} from "ol/style";
// import {Feature} from "ol";
// import {Point} from "ol/geom";
// import {Control, defaults as defaultControls} from 'ol/control';
// import {useGeographic} from "ol/proj";
// import {GeoJSON} from "ol/format";
// import {Stamen} from "ol/source";
// import {defaults} from "ol/interaction";
//
// useGeographic();
// let mapWrapper = document.getElementById('map');
//
// let pins = JSON.parse(mapWrapper.dataset.pins).map((Location) => {
//     // console.log(Location);
//     let iconFeature = new Feature({
//         geometry: new Point([Location.lon,Location.lat]),
//         name: Location.name,
//     });
//     let color = Location.color ? Location.color : Math.floor(Math.random()*16777215).toString(16);
//     let style = new Style({
//         image: new Icon({
//             anchor: [0.5, 46],
//             anchorXUnits: 'fraction',
//             anchorYUnits: 'pixels',
//             src: window.location.origin+'/map/pin/'+color.substring(1)
//         })
//     });
//     iconFeature.setStyle(style);
//     return iconFeature;
// })
//
//
// const vectorSource = new VectorSource({
//     features: pins,
// });
//
// const vectorLayer = new VectorLayer({
//     source: vectorSource,
// });
// let osm = new TileLayer({
//     source: new OSM(),
// });
// let osmgrey = new TileLayer({
//     source: new OSM(),
// });
//
// osmgrey.on('postcompose', function(event) {
//     greyscale(event.context);
// });
//
// let watercolor = new TileLayer({
//         source: new Stamen({
//             layer: 'watercolor',
//         }),
//     });
// let waterlabels = new TileLayer({
//     source: new Stamen({
//         layer: 'terrain-labels',
//     }),
// });
//
// const map = new Map({
//     target: mapWrapper,
//     layers: [
//         osmgrey,
//         // watercolor,
//         // waterlabels,
//         vectorLayer,
//     ],
//     view: new View({
//         center: [-97.2304,38.6212],
//         zoom: 7,
//     }),
//     interactions: defaults({mouseWheelZoom:false}),
// });