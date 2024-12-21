//
//  SetABCoordinator.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 04.12.2024.
//

import Foundation
import CoreLocation
import UIKit
import SwiftUI

final class SetABCoordinator: Coordinatable {
    struct ReturnData {
        let aPoint: CLLocationCoordinate2D
        let bPoint: CLLocationCoordinate2D
    }
    
    var didFinishWithAB: ((ReturnData) -> Void)?
    var didTapCloseButton: (() -> Void)?
    
    private let externalNavigationController: UINavigationController
    private let internalNavigationController: UINavigationController
    private var selectedAPoint: CLLocationCoordinate2D?
    private var selectedBPoint: CLLocationCoordinate2D?
    private let routeDataViewModel = RouteDataViewModel()
    
    init(navigationController: UINavigationController) {
        self.externalNavigationController = navigationController
        self.internalNavigationController = UINavigationController()
    }
    
    override func startCoordinator() {
        super.startCoordinator()
        presentInternalNavigationController()
        showRouteDataView()
    }
    
    private func presentInternalNavigationController() {
        internalNavigationController.modalPresentationStyle = .fullScreen
        externalNavigationController.present(internalNavigationController, animated: true, completion: nil)
    }
    
    private func dismissInternalNavigationController() {
        externalNavigationController.dismiss(animated: true, completion: nil)
    }
    
    private func showRouteDataView() {
        let routeDataView = RouteDataView(viewModel: routeDataViewModel)
        let hostingVC = UIHostingController(rootView: routeDataView)
        
        hostingVC.navigationItem.rightBarButtonItem = UIBarButtonItem(
            barButtonSystemItem: .close,
            target: self,
            action: #selector(handleCloseButtonTapped)
        )
        
        routeDataViewModel.eventHandler = { [weak self] event in
            switch event {
            case .didTapSetStartPoint:
                self?.showSetPointView(isStartPoint: true)
            case .didTapSetEndPoint:
                self?.showSetPointView(isStartPoint: false)
            case .didTapNext:
                if let aPoint = self?.selectedAPoint, let bPoint = self?.selectedBPoint {
                    self?.didFinishWithAB?(ReturnData(aPoint: aPoint, bPoint: bPoint))
                    self?.dismissInternalNavigationController()
                }
            }
        }
        
        internalNavigationController.setViewControllers([hostingVC], animated: false)
    }
    
    private func showSetPointView(isStartPoint: Bool) {
        let setPointVC = SetPointViewController()
        
        setPointVC.didTapDoneWithPoint = { [weak self] coordinate in
            if isStartPoint {
                self?.selectedAPoint = coordinate
                self?.routeDataViewModel.setCoordinateAOnButton(coordinate)
            } else {
                self?.selectedBPoint = coordinate
                self?.routeDataViewModel.setCoordinateBOnButton(coordinate)
            }
            self?.updateNextButtonState()
            self?.internalNavigationController.popViewController(animated: true)
        }
        
        internalNavigationController.pushViewController(setPointVC, animated: true)
    }
    
    private func updateNextButtonState() {
        let isActive = selectedAPoint != nil && selectedBPoint != nil
        if isActive {
            routeDataViewModel.setActiveBottomButton()
        } else {
            routeDataViewModel.setDisabledBottomButton()
        }
    }
    
    @objc private func handleCloseButtonTapped() {
        didTapCloseButton?()
        dismissInternalNavigationController()
    }
}
