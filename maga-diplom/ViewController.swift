//
//  ViewController.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 13.11.2024.
//

import UIKit
import GoogleMaps

class MapViewController: UIViewController {
    override func viewDidLoad() {
         super.viewDidLoad()
         // Do any additional setup after loading the view.
         // Create a GMSCameraPosition that tells the map to display the
         // coordinate -33.86,151.20 at zoom level 6.

         let options = GMSMapViewOptions()
         options.camera = GMSCameraPosition.camera(withLatitude: -33.86, longitude: 151.20, zoom: 6.0)
         options.frame = self.view.bounds

         let mapView = GMSMapView(options: options)
         self.view.addSubview(mapView)

         // Creates a marker in the center of the map.
         let marker = GMSMarker()
         marker.position = CLLocationCoordinate2D(latitude: -33.86, longitude: 151.20)
         marker.title = "Sydney"
         marker.snippet = "Australia"
         marker.map = mapView
   }
}


class Map2ViewController: UIViewController {
    var mapView: GMSMapView!

    override func viewDidLoad() {
        super.viewDidLoad()
        
        // Устанавливаем начальную позицию карты
        let camera = GMSCameraPosition.camera(withLatitude: 37.7749, longitude: -122.4194, zoom: 10)
        mapView = GMSMapView.map(withFrame: self.view.bounds, camera: camera)
        self.view.addSubview(mapView)

        // Координаты начальной и конечной точки
        let origin = CLLocationCoordinate2D(latitude: 37.7749, longitude: -122.4194) // A
        let destination = CLLocationCoordinate2D(latitude: 37.7849, longitude: -122.4094) // B

        fetchRoute(from: origin, to: destination)
    }

    // Функция запроса к Google Directions API
    func fetchRoute(from origin: CLLocationCoordinate2D, to destination: CLLocationCoordinate2D) {
        let originString = "\(origin.latitude),\(origin.longitude)"
        let destinationString = "\(destination.latitude),\(destination.longitude)"
        let apiKey = Constants.googleApiKey

        let urlString = "https://maps.googleapis.com/maps/api/directions/json?origin=\(originString)&destination=\(destinationString)&key=\(apiKey)"
        
        guard let url = URL(string: urlString) else { return }

        URLSession.shared.dataTask(with: url) { (data, response, error) in
            guard let data = data, error == nil else { return }
            do {
                if let json = try JSONSerialization.jsonObject(with: data, options: []) as? [String: Any],
                   let routes = json["routes"] as? [[String: Any]],
                   let route = routes.first,
                   let overviewPolyline = route["overview_polyline"] as? [String: Any],
                   let polylineString = overviewPolyline["points"] as? String {

                    DispatchQueue.main.async {
                        self.drawPath(with: polylineString)
                    }
                }
            } catch {
                print("Ошибка парсинга данных: \(error)")
            }
        }.resume()
    }

    func drawPath(with polylineString: String) {
        let path = GMSPath(fromEncodedPath: polylineString)
        let polyline = GMSPolyline(path: path)
        polyline.strokeColor = .red
        polyline.strokeWidth = 10.0
        polyline.map = mapView
        
    }
}
