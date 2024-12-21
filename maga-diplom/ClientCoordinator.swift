//
//  ClientCoordinator.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 05.12.2024.
//

import Foundation
import UIKit
import SwiftUI

class ClientCoordinator: Coordinatable {
    private(set) var navigationController = UINavigationController()
    
    override func startCoordinator() {
        super.startCoordinator()
        let startVC = UIViewController()
        startVC.view.backgroundColor = .systemBackground
        navigationController.setViewControllers([startVC], animated: false)
        LoaderOverlayProvider.shared.overlay()
        DispatchQueue.main.asyncAfter(deadline: .now() + 1, execute: {
            LoaderOverlayProvider.shared.remove()
            self.showMainMenu()
        })
    }
    
    private func showMainMenu() {
        let viewModel = MainMenuViewModel()
        viewModel.eventOutputHandler = { [weak self] event in
            self?.handleMainMenuEvent(event)
        }
        let mainMenuView = MainMenuView(viewModel: viewModel)
        let hostingController = UIHostingController(rootView: mainMenuView)
        navigationController.setViewControllers([hostingController], animated: false)
    }
    
    private func handleMainMenuEvent(_ event: MainMenuEvent) {
        switch event {
        case .startRoute:
            let abCoordinator = SetABCoordinator(navigationController: self.navigationController)
            abCoordinator.didFinishWithAB = {
                [weak self] result in
                guard let self = self else { return }
                let scenarioVC = RouteViewControllerScenario(scenario: ScenarioDataProvider.clientScenarioActions())
                let nvc = ClosableNavigationController().onlyFirst()
                nvc.pushViewController(scenarioVC, animated: false)
                self.setNavigationBarBlur(nvc)
                nvc.modalPresentationStyle = .fullScreen
                LoaderOverlayProvider.shared.overlay()
                DispatchQueue.main.asyncAfter(deadline: .now() + 2, execute: {
                    LoaderOverlayProvider.shared.remove()
                    self.navigationController.present(nvc, animated: true)
                })
            }
            add(coordinatable: abCoordinator)
        case .setObstaclePoint:
            print("2")
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
