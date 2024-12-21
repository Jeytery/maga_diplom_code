//
//  RouteViewControllerScenario.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 05.12.2024.
//

import Foundation
import UIKit
import GoogleMaps
import AlertKit

final class RouteViewControllerScenario: UIViewController {
    
    var polylinesColor: UIColor = .systemIndigo
    
    private var mapView: GMSMapView!
    private let scenario: [ScenarioAction]
    private var currentPolyline: GMSPolyline!
    private var currentSubRoutePolyline: GMSPolyline!
    private var updateButtonValue: Int = 0
    private let updatesButton = UIButton(type: .system)
    private var currentPositionMarker: GMSMarker!
 
    init(scenario: [ScenarioAction]) {
        self.scenario = scenario
        super.init(nibName: nil, bundle: nil)
    }
    
    required init?(coder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        setupMapView()
        setupUpdatesButton()
        startScenario(scenario)
    }
    
    private func startScenario(_ scenario: [ScenarioAction]) {
        guard let firstAction = scenario.first else { return } // end reccursion
        let arr = Array(scenario.dropFirst())
        switch firstAction {
        case .showRoute(let input):
            self.drawRoute(coordinates: input.directionCoordinates)
            self.incrementUpdateButton()
        case .waitFor(let time):
            DispatchQueue.main.asyncAfter(deadline: .now() + Double(time), execute: {
                [weak self] in
                self?.startScenario(arr)
            })
            return
        case .showSubRoute(let input):
            self.drawSubRoute(coordinates: input.directionCoordinates)
            if let movingPart = input.movingPart {
                startMovingPart(movingPart, directionCoordinates: input.directionCoordinates, currentValue: 0)
                DispatchQueue.main.asyncAfter(deadline: .now() + Double(movingPart.movingTime), execute: {
                    [weak self] in
                    self?.startScenario(arr)
                })
                return
            }
        case .showPoint(let input):
            self.addMarker(at: input.coordinate, title: "", color: input.color)
            self.incrementUpdateButton()
            
        case .showYourRouteAreDeletedAlert:
            showYourRouteHasBeenDeletedAlert()
            
        case .showDeleteRouteAlert:
            func showRoutesDeletedAlert(on viewController: UIViewController) {
                let alert = UIAlertController(
                    title: "Delete this route?",
                    message: "",
                    preferredStyle: .alert
                )
                
                let okAction = UIAlertAction(title: "Delete", style: .destructive, handler: {_ in 
                    self.mapView.clear()
                    self.addMarker(at: ScenarioDataProvider.specialPoint1, title: "", color: .red)
                    self.addMarker(at: ScenarioDataProvider.specialPoint2, title: "", color: .red)
                    self.addMarker(at: ScenarioDataProvider.specialPoint3, title: "", color: .red)
                })
                let cancelAction  = UIAlertAction(title: "Cancel", style: .cancel, handler: nil)
                alert.addAction(okAction)
                alert.addAction(cancelAction)
                viewController.present(alert, animated: true, completion: nil)
            }
            showRoutesDeletedAlert(on: self)
        default: break
        }
        startScenario(arr)
    }
    
    func moveMarker(marker: GMSMarker, to newPosition: CLLocationCoordinate2D, duration: TimeInterval = 1.0, handler: @escaping () -> Void) {
        CATransaction.begin()
        CATransaction.setAnimationDuration(duration)
        marker.position = newPosition
        CATransaction.commit()
        DispatchQueue.main.asyncAfter(deadline: .now() + duration, execute: {
            handler()
        })
    }
    
    private func startMovingPart(_ movingPart: MovingPart, directionCoordinates: [CLLocationCoordinate2D], currentValue: Int) {
        self.currentSubRoutePolyline.map = nil
        drawSubRoute(coordinates: directionCoordinates)
        if currentValue == movingPart.movingCoordinatesDropAmount { return } // end reccursion
        if currentPositionMarker == nil {
            self.currentPositionMarker = getCurrentPositionMarker(position: directionCoordinates.first ?? .init())
        }
        moveMarker(marker: currentPositionMarker, to: directionCoordinates[1], duration: TimeInterval(movingPart.movingTime / movingPart.movingCoordinatesDropAmount), handler: {
            self.startMovingPart(movingPart, directionCoordinates: Array(directionCoordinates.dropFirst()), currentValue: currentValue + 1)
        })
    }
    
    private func incrementUpdateButton() {
        self.updateButtonValue += 1
        updatesButton.setTitle("Updates(\(self.updateButtonValue))", for: .normal)
    }
    
    @objc private func setPointButtonDidTap() {
        let setPointVC = SetPointViewController()
        let nvc = ClosableNavigationController().onlyFirst()
        nvc.pushViewController(setPointVC, animated: false)
        nvc.modalPresentationStyle = .fullScreen
        setNavigationBarBlur(nvc)
        present(nvc, animated: true)
        setPointVC.didTapDoneWithPoint = {
            [weak self] _ in
            nvc.dismiss(animated: true)
            AlertKitAPI.present(title: "Success", subtitle: "Point has been added", icon: .done, style: .iOS16AppleMusic, haptic: .none)
        }
    }
    
    private func setNavigationBarBlur(_ navigationController: UINavigationController) {
        let appearance = UINavigationBarAppearance()
        appearance.configureWithTransparentBackground()
        appearance.backgroundEffect = UIBlurEffect(style: .light)
        
        navigationController.navigationBar.standardAppearance = appearance
        navigationController.navigationBar.scrollEdgeAppearance = appearance
    }
}

extension RouteViewControllerScenario {
    private func showYourRouteHasBeenDeletedAlert() {
        func showRoutesDeletedAlert(on viewController: UIViewController) {
            let alert = UIAlertController(
                title: "Routes Deleted",
                message: "Your routes have been deleted by the admin.",
                preferredStyle: .alert
            )
            
            let okAction = UIAlertAction(title: "OK", style: .default, handler: nil)
            alert.addAction(okAction)
            
            viewController.present(alert, animated: true, completion: nil)
        }
        showRoutesDeletedAlert(on: self)
    }
    
    private func setupMapView() {
        let camera = GMSCameraPosition.camera(
            withLatitude: ScenarioDataProvider.aPoint.latitude,
            longitude: ScenarioDataProvider.aPoint.longitude, zoom: 12)
        mapView = GMSMapView.map(withFrame: self.view.bounds, camera: camera)
        mapView.autoresizingMask = [.flexibleWidth, .flexibleHeight]
        self.view.addSubview(mapView)
    }
    
    private func setupUpdatesButton() {
        updatesButton.setTitle("Updates", for: .normal)
        updatesButton.setTitleColor(.white, for: .normal)
        updatesButton.backgroundColor = .systemBlue
        updatesButton.layer.cornerRadius = 20
        updatesButton.translatesAutoresizingMaskIntoConstraints = false
        updatesButton.titleLabel?.font = .systemFont(ofSize: 18, weight: .semibold)
        self.view.addSubview(updatesButton)
        let setPointButton = UIButton(type: .system)
        setPointButton.setTitle("Set Point", for: .normal)
        setPointButton.setTitleColor(.white, for: .normal)
        setPointButton.backgroundColor = .systemGreen
        setPointButton.layer.cornerRadius = 20
        setPointButton.translatesAutoresizingMaskIntoConstraints = false
        setPointButton.titleLabel?.font = .systemFont(ofSize: 18, weight: .semibold)
        self.view.addSubview(setPointButton)
        let buttonPadding: CGFloat = 10
        NSLayoutConstraint.activate([
            updatesButton.bottomAnchor.constraint(equalTo: self.view.safeAreaLayoutGuide.bottomAnchor, constant: -20),
            updatesButton.leadingAnchor.constraint(equalTo: self.view.leadingAnchor, constant: 20),
            updatesButton.trailingAnchor.constraint(equalTo: setPointButton.leadingAnchor, constant: -buttonPadding),
            updatesButton.heightAnchor.constraint(equalToConstant: 50),
            updatesButton.widthAnchor.constraint(equalTo: setPointButton.widthAnchor),
            setPointButton.bottomAnchor.constraint(equalTo: self.view.safeAreaLayoutGuide.bottomAnchor, constant: -20),
            setPointButton.trailingAnchor.constraint(equalTo: self.view.trailingAnchor, constant: -20),
            setPointButton.heightAnchor.constraint(equalToConstant: 50),
        ])
        updatesButton.addTarget(self, action: #selector(addButtonDidTap), for: .touchUpInside)
        setPointButton.addTarget(self, action: #selector(setPointButtonDidTap), for: .touchUpInside)
    }
    
    @objc private func addButtonDidTap() {
        //mapView.clear()
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
    
    private func addCurrentLocationMarker(at position: CLLocationCoordinate2D) {
        let currentLocationIcon = createCurrentLocationIcon()
        let marker = GMSMarker(position: position)
        marker.icon = currentLocationIcon
        marker.map = mapView
    }
    
    private func drawSubRoute(coordinates: [CLLocationCoordinate2D]) {
        self.currentSubRoutePolyline?.map = nil
        var path = GMSMutablePath()
        coordinates.forEach({
            path.add($0)
        })
        let routePolyline = GMSPolyline(path: path)
        self.currentSubRoutePolyline = routePolyline
        routePolyline.strokeWidth = 14
        routePolyline.strokeColor = polylinesColor.withAlphaComponent(0.7)
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
    
    private func getCurrentPositionMarker(position: CLLocationCoordinate2D) -> GMSMarker {
        let marker = GMSMarker()
        marker.icon = createCurrentLocationIcon()
        marker.position = position
        marker.map = mapView
        return marker
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
    
    private func drawRoute(coordinates: [CLLocationCoordinate2D]) {
        currentPolyline?.map = nil
        var path = GMSMutablePath()
        coordinates.forEach({
            path.add($0)
        })
        let routePolyline = GMSPolyline(path: path)
        currentPolyline = routePolyline
        routePolyline.strokeWidth = 5
        routePolyline.strokeColor = polylinesColor
        routePolyline.map = mapView
        addMarker(at: coordinates.first ?? .init(), title: "", color: polylinesColor)
        addMarker(at: coordinates.last ?? .init(), title: "", color: polylinesColor)
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
