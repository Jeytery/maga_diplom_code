//
//  ManyPointsCoordinator.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 05.12.2024.
//

import UIKit
import GoogleMaps

class ManyPointsCoordinator {
    private let navigationController: UINavigationController
    private var selectedPoints: [CLLocationCoordinate2D] = []
    private unowned var setPointViewController: SetPointViewController!
    
    var didFinish: (([CLLocationCoordinate2D]) -> Void)?
    
    init(navigationController: UINavigationController) {
        self.navigationController = navigationController
    }
    
    func start() {
        let setPointViewController = SetPointViewController()
        self.setPointViewController = setPointViewController 
        let navController = UINavigationController(rootViewController: setPointViewController)
        navController.modalPresentationStyle = .fullScreen
        setPointViewController.didTapDoneWithPoint = { [weak self] point in
            guard let self = self else { return }
            self.selectedPoints.append(point)
        }
        let okButton = UIBarButtonItem(title: "OK", style: .done, target: self, action: #selector(okButtonTapped))
        setPointViewController.navigationItem.rightBarButtonItem = okButton
        setPointViewController.navigationItem.title = "Select Points"
        let removeLastButton = UIBarButtonItem(title: "Remove last", style: .done, target: self, action: #selector(removeLastDidTap))
        setPointViewController.navigationItem.leftBarButtonItem = removeLastButton
        navigationController.present(navController, animated: true, completion: nil)
    }
    
    @objc private func removeLastDidTap() {
        if selectedPoints.isEmpty { return }
        if setPointViewController.lastMarker?.map == nil { return }
        setPointViewController.lastMarker?.map = nil
        selectedPoints.removeLast()
    }
    
    @objc private func okButtonTapped() {
        navigationController.dismiss(animated: true) { [weak self] in
            guard let self = self else { return }
            self.didFinish?(self.selectedPoints)
        }
    }
}

