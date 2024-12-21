import UIKit
import GoogleMaps

final class RouteViewController: UIViewController {
    private var mapView: GMSMapView!
    private var avoidPoints: [CLLocationCoordinate2D] = []
    private var startPoint: CLLocationCoordinate2D!
    private var endPoint: CLLocationCoordinate2D!
    
    private var randomGenerateKievPointRadious: Double = 9000
    
    override func viewDidLoad() {
        super.viewDidLoad()
        setupMapView()
        setupUpdatesButton()
        generateRoutePoints()
        fetchAndDrawRoute()
    }
    
    private func setupMapView() {
        let camera = GMSCameraPosition.camera(withLatitude: 50.4501, longitude: 30.5234, zoom: 12)
        mapView = GMSMapView.map(withFrame: self.view.bounds, camera: camera)
        mapView.autoresizingMask = [.flexibleWidth, .flexibleHeight]
        self.view.addSubview(mapView)
    }
    
    private func setupUpdatesButton() {
        let updatesButton = UIButton(type: .system)
        updatesButton.setTitle("Updates", for: .normal)
        updatesButton.setTitleColor(.white, for: .normal)
        updatesButton.backgroundColor = .systemBlue
        updatesButton.layer.cornerRadius = 20
        updatesButton.translatesAutoresizingMaskIntoConstraints = false
        updatesButton.titleLabel?.font = .systemFont(ofSize: 18, weight: .semibold)
        self.view.addSubview(updatesButton)
        NSLayoutConstraint.activate([
            updatesButton.bottomAnchor.constraint(equalTo: self.view.safeAreaLayoutGuide.bottomAnchor, constant: -20),
            updatesButton.leadingAnchor.constraint(equalTo: self.view.leadingAnchor, constant: 20),
            updatesButton.trailingAnchor.constraint(equalTo: self.view.trailingAnchor, constant: -20),
            updatesButton.heightAnchor.constraint(equalToConstant: 50)
        ])
        
        updatesButton.addTarget(self, action: #selector(addButtonDidTap), for: .touchUpInside)
    }
    
    @objc private func addButtonDidTap() {
        mapView.clear()
        generateRoutePoints()
        fetchAndDrawRoute()
    }

    private func generateRoutePoints() {
        startPoint = generateRandomPoint(around: CLLocationCoordinate2D(latitude: 50.4501, longitude: 30.5234), radius: randomGenerateKievPointRadious)
        endPoint = generateRandomPoint(around: CLLocationCoordinate2D(latitude: 50.4501, longitude: 30.5234), radius: randomGenerateKievPointRadious)

        addMarker(at: startPoint, title: "Start Point", color: .systemIndigo)
        addMarker(at: endPoint, title: "End Point", color: .systemIndigo)
    }
    
    private func generateRandomPoint(around center: CLLocationCoordinate2D, radius: Double) -> CLLocationCoordinate2D {
        let earthRadius = 6371000.0
        let randomDistance = Double.random(in: 0...radius)
        let randomBearing = Double.random(in: 0...(2 * Double.pi))

        let lat1 = center.latitude * Double.pi / 180
        let lon1 = center.longitude * Double.pi / 180

        let lat2 = asin(sin(lat1) * cos(randomDistance / earthRadius) +
                        cos(lat1) * sin(randomDistance / earthRadius) * cos(randomBearing))
        let lon2 = lon1 + atan2(sin(randomBearing) * sin(randomDistance / earthRadius) * cos(lat1),
                                cos(randomDistance / earthRadius) - sin(lat1) * sin(lat2))

        return CLLocationCoordinate2D(latitude: lat2 * 180 / Double.pi, longitude: lon2 * 180 / Double.pi)
    }

    private func addMarker(at position: CLLocationCoordinate2D, title: String, color: UIColor) {
        let marker = GMSMarker(position: position)
        marker.title = title
        marker.icon = GMSMarker.markerImage(with: color)
        marker.map = mapView
    }

    private func fetchAndDrawRoute() {
        var urlString = "https://maps.googleapis.com/maps/api/directions/json?"
        urlString += "origin=\(startPoint.latitude),\(startPoint.longitude)"
        urlString += "&destination=\(endPoint.latitude),\(endPoint.longitude)"

        if !avoidPoints.isEmpty {
            let avoidLocations = avoidPoints.map { "\($0.latitude),\($0.longitude)" }
            urlString += "&avoid=\(avoidLocations.joined(separator: "|"))"
        }

        urlString += "&key=\(Constants.googleApiKey)"

        guard let url = URL(string: urlString) else { return }

        let task = URLSession.shared.dataTask(with: url) { data, _, error in
            guard let data = data, error == nil else { return }
            do {
                if let json = try JSONSerialization.jsonObject(with: data, options: []) as? [String: Any],
                   let routes = json["routes"] as? [[String: Any]],
                   let legs = routes.first?["legs"] as? [[String: Any]],
                   let steps = legs.first?["steps"] as? [[String: Any]],
                   let firstStep = legs.first?["steps"] as? [[String: Any]],
                   let firstStepStartLocation = firstStep.first?["start_location"] as? [String: Any],
                   let firstLat = firstStepStartLocation["lat"] as? CLLocationDegrees,
                   let firstLng = firstStepStartLocation["lng"] as? CLLocationDegrees,
                   let lastStep = legs.last?["steps"] as? [[String: Any]],
                   let lastStepEndLocation = lastStep.last?["end_location"] as? [String: Any],
                   let lastLat = lastStepEndLocation["lat"] as? CLLocationDegrees,
                   let lastLng = lastStepEndLocation["lng"] as? CLLocationDegrees,
                   let overviewPolyline = routes.first?["overview_polyline"] as? [String: Any],
                   let points = overviewPolyline["points"] as? String
                {
                    guard let path = GMSPath(fromEncodedPath: points) else { return }
                    let firstRoutePoint = CLLocationCoordinate2D(latitude: firstLat, longitude: firstLng)
                    let lastRoutePoint = CLLocationCoordinate2D(latitude: lastLat, longitude: lastLng)
                    let midIndex = steps.count / 2
                    let middleStep = steps[midIndex]
                    let middleLocation = middleStep["start_location"] as? [String: CLLocationDegrees]
                    let midLat = middleLocation?["lat"] ?? 0
                    let midLng = middleLocation?["lng"] ?? 0
                    let middlePoint = CLLocationCoordinate2D(latitude: midLat, longitude: midLng)
                    let randomIndex = Int.random(in: 1..<Int(path.count()))
                    let randomPoint = path.coordinate(at: UInt(randomIndex))
                    DispatchQueue.main.async {
                        [weak self] in
                        guard let self = self else { return }
                        self.drawRoute(with: points)
                        if self.startPoint.latitude != firstRoutePoint.latitude || self.startPoint.longitude != firstRoutePoint.longitude {
                            self.drawDashedLine(from: self.startPoint, to: firstRoutePoint, color: .systemRed)
                        }
                        if self.endPoint.latitude != lastRoutePoint.latitude || self.endPoint.longitude != lastRoutePoint.longitude {
                            self.drawDashedLine(from: lastRoutePoint, to: self.endPoint, color: .systemIndigo)
                        }
                        self.addRandomMarkers(around: self.startPoint, title: "Around Start")
                        self.addRandomMarkers(around: middlePoint, title: "Around Middle")
                        self.addRandomMarkers(around: self.endPoint, title: "Around End")
                        let subPath = GMSMutablePath()
                        self.addCurrentLocationMarker(at: randomPoint)
                        for i in randomIndex..<Int(path.count()) {
                            subPath.add(path.coordinate(at: UInt(i)))
                        }
                        self.drawSubRoute(with: subPath)
                    }
                }
            } catch {
                print(error)
            }
        }
        task.resume()
    }
    
    private func addRandomMarkers(around point: CLLocationCoordinate2D, title: String) {
        for _ in 1...2 {
            let randomPoint = generateRandomPoint(around: point, radius: 700)
            addMarker(at: randomPoint, title: title, color: .systemRed)
        }
    }

    private func addCurrentLocationMarker(at position: CLLocationCoordinate2D) {
        let currentLocationIcon = createCurrentLocationIcon()
        let marker = GMSMarker(position: position)
        marker.icon = currentLocationIcon
        marker.map = mapView
    }
    
    private func drawSubRoute(with path: GMSMutablePath) {
        let routePolyline = GMSPolyline(path: path)
        routePolyline.strokeWidth = 14
        routePolyline.strokeColor = .systemIndigo.withAlphaComponent(0.7)
        routePolyline.map = mapView
    }

    private func createCurrentLocationIcon() -> UIImage {
        let size: CGFloat = 30
        let outerCircle = UIBezierPath(arcCenter: CGPoint(x: size / 2, y: size / 2), radius: size / 2, startAngle: 0, endAngle: .pi * 2, clockwise: true)
        let innerCircle = UIBezierPath(arcCenter: CGPoint(x: size / 2, y: size / 2), radius: size / 3, startAngle: 0, endAngle: .pi * 2, clockwise: true)

        let imageSize = CGSize(width: size, height: size)
        UIGraphicsBeginImageContextWithOptions(imageSize, false, 0)
        
        UIColor.white.setFill()
        outerCircle.fill()
        
        UIColor.systemBlue.setFill()
        innerCircle.fill()

        let markerImage = UIGraphicsGetImageFromCurrentImageContext()
        UIGraphicsEndImageContext()

        return markerImage ?? UIImage()
    }
    
    
    private func createBlueIcon() -> UIImage {
        let size: CGFloat = 30
        let innerCircle = UIBezierPath(arcCenter: CGPoint(x: size / 2, y: size / 2), radius: size / 3, startAngle: 0, endAngle: .pi * 2, clockwise: true)
        let imageSize = CGSize(width: size, height: size)
        UIGraphicsBeginImageContextWithOptions(imageSize, false, 0)
        UIColor.systemBlue.setFill()
        innerCircle.fill()
        let markerImage = UIGraphicsGetImageFromCurrentImageContext()
        UIGraphicsEndImageContext()

        return markerImage ?? UIImage()
    }

    private func drawRoute(with polyline: String) {
        let path = GMSPath(fromEncodedPath: polyline)
        let routePolyline = GMSPolyline(path: path)
        routePolyline.strokeWidth = 5
        routePolyline.strokeColor = .systemIndigo
        routePolyline.map = mapView
    }
    
    private func drawDashedLine(from start: CLLocationCoordinate2D, to end: CLLocationCoordinate2D, color: UIColor) {
        let path = GMSMutablePath()
        path.add(start)
        path.add(end)
        let polyline = GMSPolyline(path: path)
        polyline.strokeWidth = 30
        let image = createBlueIcon()
        let stampStyle = GMSSpriteStyle(image: image)
        let transparentStampStroke = GMSStrokeStyle.transparentStroke(withStamp: stampStyle)
        let span = GMSStyleSpan(style: transparentStampStroke)
        polyline.spans = [span]
        polyline.zIndex = 1
        polyline.map = mapView
    }
}

