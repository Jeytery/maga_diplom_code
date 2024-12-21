//
//  RouteShowerViewController.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 04.12.2024.
//

import Foundation
import UIKit
import GoogleMaps

class GoogleDirectionsAPI {
    static func getRoute(from startPoint: CLLocationCoordinate2D,
                         to endPoint: CLLocationCoordinate2D,
                         avoiding avoidPoints: [CLLocationCoordinate2D] = [],
                         completion: @escaping (Result<[CLLocationCoordinate2D], Error>) -> Void) {
        
        var urlString = "https://maps.googleapis.com/maps/api/directions/json?"
        urlString += "origin=\(startPoint.latitude),\(startPoint.longitude)"
        urlString += "&destination=\(endPoint.latitude),\(endPoint.longitude)"
        
        if !avoidPoints.isEmpty {
            let avoidLocations = avoidPoints.map { "\($0.latitude),\($0.longitude)" }
            urlString += "&avoid=\(avoidLocations.joined(separator: "|"))"
        }
        
        urlString += "&key=\(Constants.googleApiKey)"
        
        guard let url = URL(string: urlString) else {
            DispatchQueue.main.async {
                completion(.failure(NSError(domain: "Invalid URL", code: -1, userInfo: nil)))
            }
            return
        }
        
        let task = URLSession.shared.dataTask(with: url) { data, response, error in
            if let error = error {
                DispatchQueue.main.async {
                    completion(.failure(error))
                }
                return
            }
            
            guard let data = data else {
                DispatchQueue.main.async {
                    completion(.failure(NSError(domain: "No data received", code: -1, userInfo: nil)))
                }
                return
            }
            
            do {
                if let json = try JSONSerialization.jsonObject(with: data, options: []) as? [String: Any],
                   let routes = json["routes"] as? [[String: Any]],
                   let firstRoute = routes.first,
                   let overviewPolyline = firstRoute["overview_polyline"] as? [String: Any],
                   let points = overviewPolyline["points"] as? String {
                    
                    let path = GMSPath(fromEncodedPath: points)
                    let coordinates = path?.toArray() ?? []
                    DispatchQueue.main.async {
                        completion(.success(coordinates))
                    }
                } else {
                    DispatchQueue.main.async {
                        completion(.failure(NSError(domain: "Invalid JSON structure", code: -1, userInfo: nil)))
                    }
                }
            } catch {
                DispatchQueue.main.async {
                    completion(.failure(error))
                }
            }
        }
        
        task.resume()
    }
}

class RouteShowerViewController: UIViewController {
    private let aPoint: CLLocationCoordinate2D
    private let bPoint: CLLocationCoordinate2D
    private let avoidPoints: [CLLocationCoordinate2D]
    
    var didTapNextButton: (([CLLocationCoordinate2D]) -> Void)?
    var didTapNextButtonWithJson: ((String?) -> Void)?
    
    private var mapView: GMSMapView!
    private var loaderView: UIView!
    private var routePath: GMSPolyline?
    
    private let nextButton: UIButton = {
        let button = UIButton(type: .system)
        button.setTitle("Next", for: .normal)
        button.setTitleColor(.white, for: .normal)
        button.titleLabel?.font = UIFont.systemFont(ofSize: 16, weight: .semibold)
        button.backgroundColor = .systemBlue
        button.layer.cornerRadius = 8
        button.translatesAutoresizingMaskIntoConstraints = false
        button.addTarget(self, action: #selector(nextButtonTapped), for: .touchUpInside)
        return button
    }()
    
    init(aPoint: CLLocationCoordinate2D, bPoint: CLLocationCoordinate2D, avoidPoints: [CLLocationCoordinate2D] = []) {
        self.aPoint = aPoint
        self.bPoint = bPoint
        self.avoidPoints = avoidPoints
        super.init(nibName: nil, bundle: nil)
    }
    
    required init?(coder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        view.backgroundColor = .white
        setupMapView()
        setupLoaderView()
        setupConstraints()
        drawRoute()
    }
    
    private func setupMapView() {
        let camera = GMSCameraPosition.camera(withLatitude: aPoint.latitude, longitude: aPoint.longitude, zoom: 12)
        mapView = GMSMapView.map(withFrame: .zero, camera: camera)
        mapView.translatesAutoresizingMaskIntoConstraints = false
        view.addSubview(mapView)
        addMarker(at: aPoint, color: .systemRed, shape: .circle)
        addMarker(at: bPoint, color: .systemBlue, shape: .circle)
    }
    
    private func setupLoaderView() {
        loaderView = UIView()
        loaderView.backgroundColor = UIColor(white: 0, alpha: 0.4)
        loaderView.translatesAutoresizingMaskIntoConstraints = false
        loaderView.isHidden = true
        
        let activityIndicator = UIActivityIndicatorView(style: .large)
        activityIndicator.color = .white
        activityIndicator.translatesAutoresizingMaskIntoConstraints = false
        loaderView.addSubview(activityIndicator)
        activityIndicator.centerXAnchor.constraint(equalTo: loaderView.centerXAnchor).isActive = true
        activityIndicator.centerYAnchor.constraint(equalTo: loaderView.centerYAnchor).isActive = true
        activityIndicator.startAnimating()
        
        view.addSubview(loaderView)
    }
    
    private func setupConstraints() {
        view.addSubview(nextButton)
        
        NSLayoutConstraint.activate([
            mapView.topAnchor.constraint(equalTo: view.topAnchor),
            mapView.leadingAnchor.constraint(equalTo: view.leadingAnchor),
            mapView.trailingAnchor.constraint(equalTo: view.trailingAnchor),
            mapView.bottomAnchor.constraint(equalTo: nextButton.topAnchor, constant: -16),
            
            nextButton.leadingAnchor.constraint(equalTo: view.leadingAnchor, constant: 16),
            nextButton.trailingAnchor.constraint(equalTo: view.trailingAnchor, constant: -16),
            nextButton.bottomAnchor.constraint(equalTo: view.safeAreaLayoutGuide.bottomAnchor, constant: -16),
            nextButton.heightAnchor.constraint(equalToConstant: 50),
            
            loaderView.topAnchor.constraint(equalTo: view.topAnchor),
            loaderView.leadingAnchor.constraint(equalTo: view.leadingAnchor),
            loaderView.trailingAnchor.constraint(equalTo: view.trailingAnchor),
            loaderView.bottomAnchor.constraint(equalTo: view.bottomAnchor)
        ])
    }
    
    private func addMarker(at coordinate: CLLocationCoordinate2D, color: UIColor, shape: Shape) {
        let marker = GMSMarker()
        marker.position = coordinate
        marker.icon = shape == .circle ? GMSMarker.markerImage(with: color) : createSquareMarker(color: color)
        marker.map = mapView
    }
    
    private func createSquareMarker(color: UIColor) -> UIImage? {
        let size: CGFloat = 30
        UIGraphicsBeginImageContext(CGSize(width: size, height: size))
        guard let context = UIGraphicsGetCurrentContext() else { return nil }
        
        context.setFillColor(color.cgColor)
        context.fill(CGRect(x: 0, y: 0, width: size, height: size))
        
        let image = UIGraphicsGetImageFromCurrentImageContext()
        UIGraphicsEndImageContext()
        return image
    }
    
    private func drawRoute() {
        showLoader()
        GoogleDirectionsAPI.getRoute(from: aPoint, to: bPoint, avoiding: avoidPoints) { [weak self] result in
            guard let self = self else { return }
            self.hideLoader()
            switch result {
            case .success(let points):
                self.displayRoute(with: points)
                avoidPoints.forEach {
                    self.addMarker(at: $0, color: .green, shape: .circle)
                }
            case .failure(let error):
                print("Failed to get route: \(error)")
            }
        }
    }
    
    private func displayRoute(with points: [CLLocationCoordinate2D]) {
        let path = GMSMutablePath()
        points.forEach { path.add($0) }
        
        routePath?.map = nil
        routePath = GMSPolyline(path: path)
        routePath?.strokeColor = .systemTeal
        routePath?.strokeWidth = 5
        routePath?.map = mapView
    }
    
    private func showLoader() {
        loaderView.isHidden = false
    }
    
    private func hideLoader() {
        loaderView.isHidden = true
    }
    
    @objc private func nextButtonTapped() {
        guard let points = routePath?.path?.toArray() else { return }
        didTapNextButton?(points)
        let jsonObjects = points.map { ["latitude": $0.latitude, "longitude": $0.longitude] }
        if let jsonData = try? JSONSerialization.data(withJSONObject: jsonObjects, options: []),
           let jsonString = String(data: jsonData, encoding: .utf8) 
        {
            didTapNextButtonWithJson?(jsonString)
        }
    }
}

// Extension to convert GMSPath to an array of CLLocationCoordinate2D
extension GMSPath {
    func toArray() -> [CLLocationCoordinate2D] {
        return (0..<count()).map { coordinate(at: $0) }
    }
}

// Helper enum for marker shapes
private enum Shape {
    case circle, square
}
