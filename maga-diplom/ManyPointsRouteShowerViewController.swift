//
//  ManyPointsRouteShowerViewController.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 04.12.2024.
//

import Foundation
import UIKit
import GoogleMaps

class ManyPointsRouteShowerViewController: UIViewController {
    private let points: [CLLocationCoordinate2D]
    
    var didTapNextButton: (([CLLocationCoordinate2D]) -> Void)?
    
    private var mapView: GMSMapView!
    private var loaderView: UIView!
    private var routePaths: [GMSPolyline] = []
    
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
    
    init(points: [CLLocationCoordinate2D]) {
        self.points = points
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
        guard let firstPoint = points.first else { return }
        let camera = GMSCameraPosition.camera(withLatitude: firstPoint.latitude, longitude: firstPoint.longitude, zoom: 12)
        mapView = GMSMapView.map(withFrame: .zero, camera: camera)
        mapView.translatesAutoresizingMaskIntoConstraints = false
        view.addSubview(mapView)
        
        for (index, point) in points.enumerated() {
            addMarker(at: point, color: .systemBlue, label: "\(index + 1)")
        }
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
        NSLayoutConstraint.activate([
            activityIndicator.centerXAnchor.constraint(equalTo: loaderView.centerXAnchor),
            activityIndicator.centerYAnchor.constraint(equalTo: loaderView.centerYAnchor)
        ])
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
    
    private func addMarker(at coordinate: CLLocationCoordinate2D, color: UIColor, label: String) {
        let marker = GMSMarker()
        marker.position = coordinate
        marker.icon = createLabeledMarker(color: color, label: label)
        marker.map = mapView
    }
    
    private func createLabeledMarker(color: UIColor, label: String) -> UIImage? {
        let size: CGFloat = 30
        UIGraphicsBeginImageContextWithOptions(CGSize(width: size, height: size), false, 0)
        guard let context = UIGraphicsGetCurrentContext() else { return nil }
        
        context.setFillColor(color.cgColor)
        context.fillEllipse(in: CGRect(x: 0, y: 0, width: size, height: size))
        
        let attributes: [NSAttributedString.Key: Any] = [
            .font: UIFont.systemFont(ofSize: 12, weight: .bold),
            .foregroundColor: UIColor.white
        ]
        let textSize = label.size(withAttributes: attributes)
        let textRect = CGRect(
            x: (size - textSize.width) / 2,
            y: (size - textSize.height) / 2,
            width: textSize.width,
            height: textSize.height
        )
        label.draw(in: textRect, withAttributes: attributes)
        
        let image = UIGraphicsGetImageFromCurrentImageContext()
        UIGraphicsEndImageContext()
        return image
    }
    
    private func drawRoute() {
        guard points.count > 1 else { return }
        showLoader()
        
        var fullRouteCoordinates: [CLLocationCoordinate2D] = []
        let group = DispatchGroup()
        
        for i in 0..<(points.count - 1) {
            group.enter()
            GoogleDirectionsAPI.getRoute(from: points[i], to: points[i + 1]) { [weak self] result in
                defer { group.leave() }
                guard let self = self else { return }
                switch result {
                case .success(let routeCoordinates):
                    fullRouteCoordinates.append(contentsOf: routeCoordinates)
                    self.displayRouteSegment(with: routeCoordinates)
                case .failure(let error):
                    print("Failed to get route: \(error)")
                }
            }
        }
        
        group.notify(queue: .main) { [weak self] in
            guard let self = self else { return }
            self.hideLoader()
            self.didTapNextButton?(fullRouteCoordinates)
        }
    }
    
    private func displayRouteSegment(with points: [CLLocationCoordinate2D]) {
        let path = GMSMutablePath()
        points.forEach { path.add($0) }
        
        let routeSegment = GMSPolyline(path: path)
        routeSegment.strokeColor = .systemTeal
        routeSegment.strokeWidth = 5
        routeSegment.map = mapView
        routePaths.append(routeSegment)
    }
    
    private func showLoader() {
        loaderView.isHidden = false
    }
    
    private func hideLoader() {
        loaderView.isHidden = true
    }
    
    @objc private func nextButtonTapped() {
        guard let firstRoute = routePaths.first?.path else { return }
        let fullCoordinates = (0..<firstRoute.count()).compactMap { firstRoute.coordinate(at: $0) }
        didTapNextButton?(fullCoordinates)
    }
}
