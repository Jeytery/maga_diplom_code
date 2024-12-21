//
//  DashboardViewController.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 23.11.2024.
//

import UIKit
import GoogleMaps

class DashboardViewController: UIViewController {
    private var mapView: GMSMapView!
    private let buttonHeight: CGFloat = 50
    private let buttonSpacing: CGFloat = 10
    private let buttonPadding: CGFloat = 16
    private var routes: [[CLLocationCoordinate2D]] = []
    private var avoidPointsPerRoute: [[CLLocationCoordinate2D]] = []
    private let routeColors: [UIColor] = [
        .systemBlue,
        .systemGreen,
        .systemPurple,
        .systemOrange,
        .systemPink,
        .systemTeal
    ]
    
    private var currentStartPoint: CLLocationCoordinate2D!
    private var currentEndPoint: CLLocationCoordinate2D!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        setupMapView()
        setupBottomButtons()
        generateRoutesAndMarkers()
    }
    
    private func setupMapView() {
        let camera = GMSCameraPosition.camera(withLatitude: 50.4501, longitude: 30.5234, zoom: 11)
        mapView = GMSMapView.map(withFrame: self.view.bounds, camera: camera)
        mapView.autoresizingMask = [.flexibleWidth, .flexibleHeight]
        mapView.delegate = self
        self.view.addSubview(mapView)
    }
    
    private func setupBottomButtons() {
        let buttonTitles = ["Set Point", "Updates"]
        let buttonColors: [UIColor] = [.systemGreen, .systemBlue]
        let buttonWidth = (self.view.frame.width - 2 * buttonPadding - CGFloat(buttonTitles.count - 1) * buttonSpacing) / CGFloat(buttonTitles.count)
        
        for (index, title) in buttonTitles.enumerated() {
            let button = UIButton(type: .system)
            button.setTitle(title, for: .normal)
            button.setTitleColor(.white, for: .normal)
            button.backgroundColor = buttonColors[index]
            button.layer.cornerRadius = 17
            button.translatesAutoresizingMaskIntoConstraints = false
            button.titleLabel?.font = .systemFont(ofSize: 18, weight: .semibold)
            self.view.addSubview(button)
            
            NSLayoutConstraint.activate([
                button.leadingAnchor.constraint(equalTo: self.view.leadingAnchor, constant: buttonPadding + CGFloat(index) * (buttonWidth + buttonSpacing)),
                button.bottomAnchor.constraint(equalTo: self.view.safeAreaLayoutGuide.bottomAnchor, constant: -10),
                button.widthAnchor.constraint(equalToConstant: buttonWidth),
                button.heightAnchor.constraint(equalToConstant: buttonHeight)
            ])
        }
    }
    
    private func generateRoutesAndMarkers() {
        for i in 0..<4 {
            var startPoint: CLLocationCoordinate2D
            var endPoint: CLLocationCoordinate2D
            
            repeat {
                startPoint = generateRandomPoint(around: CLLocationCoordinate2D(latitude: 50.4501, longitude: 30.5234), radius: 5000)
                endPoint = generateRandomPoint(around: CLLocationCoordinate2D(latitude: 50.4501, longitude: 30.5234), radius: 5000)
            }
            while routes.contains { route in
                route.contains {
                    $0.distance(to: startPoint) < 200 || $0.distance(to: endPoint) < 200
                }
            }
            let color = self.routeColors[i % self.routeColors.count]
            fetchAndDisplayRoute(startPoint: startPoint, endPoint: endPoint, color: color)
        }
    }
    
    private func fetchAndDisplayRoute(
        startPoint: CLLocationCoordinate2D,
        endPoint: CLLocationCoordinate2D,
        color: UIColor
    ) {
        var urlString = "https://maps.googleapis.com/maps/api/directions/json?"
        urlString += "origin=\(startPoint.latitude),\(startPoint.longitude)"
        urlString += "&destination=\(endPoint.latitude),\(endPoint.longitude)"
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
                        let subPath = GMSMutablePath()
                        self.addCurrentLocationMarker(at: randomPoint)
                        for i in randomIndex..<Int(path.count()) {
                            subPath.add(path.coordinate(at: UInt(i)))
                        }
                        self.drawSubRoute(with: subPath, color: color)
                        self.drawRoute(with: points, color: color)
                        if startPoint.latitude != firstRoutePoint.latitude || startPoint.longitude != firstRoutePoint.longitude {
                            self.drawDashedLine(from: startPoint, to: firstRoutePoint, color: .systemRed)
                        }
                        if endPoint.latitude != lastRoutePoint.latitude || endPoint.longitude != lastRoutePoint.longitude {
                            self.drawDashedLine(from: lastRoutePoint, to: endPoint, color: color)
                        }
                        self.addRandomMarkers(around: startPoint, title: "Around Start")
                        self.addRandomMarkers(around: middlePoint, title: "Around Middle")
                        self.addRandomMarkers(around: endPoint, title: "Around End")
                        self.addMarker(at: startPoint, title: "", color: color, clickable: false)
                        self.addMarker(at: endPoint, title: "", color: color, clickable: false)
                        
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
            addMarker(at: randomPoint, title: title, color: .systemRed, clickable: true)
        }
    }
    
    private func drawSubRoute(with path: GMSMutablePath, color: UIColor) {
        let routePolyline = GMSPolyline(path: path)
        routePolyline.strokeWidth = 14
        routePolyline.strokeColor = color.withAlphaComponent(0.7)
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
    
    private func drawRoute(with polyline: String, color: UIColor) {
        let path = GMSPath(fromEncodedPath: polyline)
        let routePolyline = GMSPolyline(path: path)
        routePolyline.strokeWidth = 5
        routePolyline.strokeColor = color
        routePolyline.map = mapView
    }
    
    private func addCurrentLocationMarker(at position: CLLocationCoordinate2D) {
        let currentLocationIcon = createCurrentLocationIcon()
        let marker = GMSMarker(position: position)
        marker.icon = currentLocationIcon
        marker.map = mapView
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
    
    private func addMarker(at position: CLLocationCoordinate2D, title: String, color: UIColor, clickable: Bool) {
        let marker = GMSMarker(position: position)
        marker.title = title
        marker.icon = GMSMarker.markerImage(with: color)
        marker.map = mapView
        if clickable {
            marker.userData = ["latitude": position.latitude, "longitude": position.longitude]
        }
    }
    
    private func fetchRoute(start: CLLocationCoordinate2D, end: CLLocationCoordinate2D, completion: @escaping ([CLLocationCoordinate2D]?) -> Void) {
        var urlString = "https://maps.googleapis.com/maps/api/directions/json?"
        urlString += "origin=\(start.latitude),\(start.longitude)"
        urlString += "&destination=\(end.latitude),\(end.longitude)"
        urlString += "&key=\(Constants.googleApiKey)"
        
        guard let url = URL(string: urlString) else {
            completion(nil)
            return
        }
        
        let task = URLSession.shared.dataTask(with: url) { data, _, error in
            guard let data = data, error == nil else {
                completion(nil)
                return
            }
            
            do {
                if let json = try JSONSerialization.jsonObject(with: data, options: []) as? [String: Any],
                   let routes = json["routes"] as? [[String: Any]],
                   let overviewPolyline = routes.first?["overview_polyline"] as? [String: Any],
                   let points = overviewPolyline["points"] as? String {
                    let path = GMSPath(fromEncodedPath: points)
                    var routePoints: [CLLocationCoordinate2D] = []
                    for i in 0..<path!.count() {
                        routePoints.append(path!.coordinate(at: i))
                    }
                    completion(routePoints)
                } else {
                    completion(nil)
                }
            } catch {
                completion(nil)
            }
        }
        task.resume()
    }
    
    private func generateValidAvoidPoints(near route: [CLLocationCoordinate2D], minDistance: Double) -> [CLLocationCoordinate2D] {
        var points: [CLLocationCoordinate2D] = []
        guard let firstPoint = route.first else { return points }
        
        while points.count < 8 {
            let randomPoint = generateRandomPoint(around: firstPoint, radius: 1000)
            let isFarEnough = route.allSatisfy { $0.distance(to: randomPoint) > minDistance }
            if isFarEnough {
                points.append(randomPoint)
            }
        }
        
        return points
    }
}

extension DashboardViewController: GMSMapViewDelegate {
    func mapView(_ mapView: GMSMapView, didTap marker: GMSMarker) -> Bool {
        guard let userData = marker.userData as? [String: Double],
              let latitude = userData["latitude"],
              let longitude = userData["longitude"] else {
            return false
        }
        
        let modalVC = MarkerDetailViewController()
        modalVC.markerInfo = "Coordinates: \(latitude), \(longitude)"
        modalVC.modalPresentationStyle = .automatic
        
        let navigationController = UINavigationController(rootViewController: modalVC)
        navigationController.modalPresentationStyle = .formSheet
        self.present(navigationController, animated: true)
        
        return true
    }
}

class MarkerDetailViewController: UIViewController, UITableViewDataSource, UITableViewDelegate {
    var markerInfo: String = ""
    
    private let tableView = UITableView()
    
    override func viewDidLoad() {
        super.viewDidLoad()
        view.backgroundColor = .white
        self.title = "Marker Details"
        navigationItem.leftBarButtonItem = UIBarButtonItem(barButtonSystemItem: .close, target: self, action: #selector(closeDetailView))
        let label = UILabel()
        label.text = markerInfo
        label.textAlignment = .center
        label.numberOfLines = 0
        
        tableView.dataSource = self
        tableView.delegate = self
        
        tableView.translatesAutoresizingMaskIntoConstraints = false
        label.translatesAutoresizingMaskIntoConstraints = false
        view.addSubview(label)
        view.addSubview(tableView)
        
        NSLayoutConstraint.activate([
            label.topAnchor.constraint(equalTo: view.safeAreaLayoutGuide.topAnchor, constant: 16),
            label.leadingAnchor.constraint(equalTo: view.leadingAnchor, constant: 16),
            label.trailingAnchor.constraint(equalTo: view.trailingAnchor, constant: -16),
            
            tableView.topAnchor.constraint(equalTo: label.bottomAnchor, constant: 16),
            tableView.leadingAnchor.constraint(equalTo: view.leadingAnchor),
            tableView.trailingAnchor.constraint(equalTo: view.trailingAnchor),
            tableView.bottomAnchor.constraint(equalTo: view.bottomAnchor)
        ])
    }
    
    func tableView(_ tableView: UITableView, numberOfRowsInSection section: Int) -> Int {
        return 2
    }
    
    func tableView(_ tableView: UITableView, cellForRowAt indexPath: IndexPath) -> UITableViewCell {
        let cell = UITableViewCell(style: .default, reuseIdentifier: nil)
        let options = ["Marker Info", "Delete Marker"]
        cell.textLabel?.text = options[indexPath.row]
        cell.accessoryType = .disclosureIndicator
        if options[indexPath.row] == "Delete Marker" {
            cell.textLabel?.textColor = .systemRed
        }
        return cell
    }
    
    func tableView(_ tableView: UITableView, didSelectRowAt indexPath: IndexPath) {
        tableView.deselectRow(at: indexPath, animated: true)
        if indexPath.row == 0 {
            self.dismiss(animated: true)
        }
    }
    
    @objc private func closeDetailView() {
        self.dismiss(animated: true, completion: nil)
    }
}

extension CLLocationCoordinate2D {
    func distance(to coordinate: CLLocationCoordinate2D) -> Double {
        let earthRadius = 6371000.0
        let dLat = (coordinate.latitude - self.latitude) * Double.pi / 180
        let dLon = (coordinate.longitude - self.longitude) * Double.pi / 180
        let a = sin(dLat / 2) * sin(dLat / 2) +
        cos(self.latitude * Double.pi / 180) * cos(coordinate.latitude * Double.pi / 180) *
        sin(dLon / 2) * sin(dLon / 2)
        let c = 2 * atan2(sqrt(a), sqrt(1 - a))
        return earthRadius * c
    }
}
