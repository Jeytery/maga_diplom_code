//
//  SetPointViewController.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 20.11.2024.
//

import Foundation
import UIKit
import GoogleMaps
import SwiftUI

class SetPointViewController: UIViewController {
    
    var didTapDoneWithPoint: ((CLLocationCoordinate2D) -> Void)?
    
    private var mapView: GMSMapView!
    private var marker: GMSMarker!
    
    private(set) var lastMarker: GMSMarker!
    
    private let markerImageView: UIImageView = {
        let imageView = UIImageView(image: UIImage(systemName: "arrow.down"))
        imageView.contentMode = .scaleAspectFit
        imageView.translatesAutoresizingMaskIntoConstraints = false
        imageView.tintColor = .systemBlue
        imageView.layer.shadowColor = UIColor.black.cgColor
        imageView.layer.shadowOffset = CGSize(width: 0, height: 2)
        imageView.layer.shadowOpacity = 0.5
        imageView.layer.shadowRadius = 4
        return imageView
    }()
    
    private let setPointButton: UIButton = {
        let button = UIButton(type: .system)
        button.setTitle("Set Point Here", for: .normal)
        button.setTitleColor(.white, for: .normal)
        button.titleLabel?.font = UIFont.systemFont(ofSize: 16, weight: .semibold)
        button.backgroundColor = .systemBlue
        button.layer.cornerRadius = 8
        button.translatesAutoresizingMaskIntoConstraints = false
        button.addTarget(self, action: #selector(setPointButtonTapped), for: .touchUpInside)
        return button
    }()
    
    override func viewDidLoad() {
        super.viewDidLoad()
        view.backgroundColor = .white
        setupMapView()
        setupViews()
        setupConstraints()
    }
    
    private func setupMapView() {
        let camera = GMSCameraPosition.camera(
            withLatitude: ScenarioDataProvider.aPoint.latitude,
            longitude: ScenarioDataProvider.aPoint.longitude,
            zoom: 14)
        mapView = GMSMapView.map(withFrame: .zero, camera: camera)
        mapView.translatesAutoresizingMaskIntoConstraints = false
        view.addSubview(mapView)
        
        marker = GMSMarker()
        marker.icon = UIImage(systemName: "mappin.and.ellipse")
        marker.iconView = nil
        marker.position = CLLocationCoordinate2D(latitude: 50.4501, longitude: 30.5236)
        marker.map = mapView
        self.lastMarker = marker
    }
    
    private func setupViews() {
        view.addSubview(markerImageView)
        view.addSubview(setPointButton)
    }
    
    private func setupConstraints() {
        NSLayoutConstraint.activate([
            mapView.topAnchor.constraint(equalTo: view.topAnchor),
            mapView.leadingAnchor.constraint(equalTo: view.leadingAnchor),
            mapView.trailingAnchor.constraint(equalTo: view.trailingAnchor),
            mapView.bottomAnchor.constraint(equalTo: view.bottomAnchor)
        ])
        
        NSLayoutConstraint.activate([
            markerImageView.centerXAnchor.constraint(equalTo: view.centerXAnchor),
            markerImageView.centerYAnchor.constraint(equalTo: view.centerYAnchor, constant: 13),
            markerImageView.widthAnchor.constraint(equalToConstant: 40),
            markerImageView.heightAnchor.constraint(equalToConstant: 40)
        ])
        
        NSLayoutConstraint.activate([
            setPointButton.leadingAnchor.constraint(equalTo: view.leadingAnchor, constant: 16),
            setPointButton.trailingAnchor.constraint(equalTo: view.trailingAnchor, constant: -16),
            setPointButton.bottomAnchor.constraint(equalTo: view.safeAreaLayoutGuide.bottomAnchor, constant: -16),
            setPointButton.heightAnchor.constraint(equalToConstant: 50)
        ])
    }
    
    @objc private func setPointButtonTapped() {
        let centerCoordinate = mapView.camera.target
        print("Selected coordinate: \(centerCoordinate.latitude), \(centerCoordinate.longitude)")
        self.didTapDoneWithPoint?(centerCoordinate)
        let marker = GMSMarker(position: centerCoordinate)
        marker.map = mapView
    }
}

