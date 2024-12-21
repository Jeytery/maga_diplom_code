//
//  AppDelegate.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 13.11.2024.
//

import UIKit
import GoogleMaps
import SwiftUI

@main
class AppDelegate: UIResponder, UIApplicationDelegate {
    var window: UIWindow?
    private var setABCoordinator: SetABCoordinator!
    private var clientCoordinator: ClientCoordinator!
    private var adminCoordinator: AdminCoordinator!
    
    func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {
        GMSServices.provideAPIKey(Constants.googleApiKey)
        window = .init(frame: UIScreen.main.bounds)
        //startGetABAndShowRouteFlow()
        //startDebugMenu()
        startClient()
        //startAdmin()
        
        
//        let setPointViewController = SetPointViewController()
//        let navigationController = UINavigationController(rootViewController: setPointViewController)
//        
//        let closeButton = UIBarButtonItem(barButtonSystemItem: .close, target: self, action: #selector(closeButtonTapped))
//        closeButton.tintColor = .red
//        
//        setPointViewController.navigationItem.rightBarButtonItem = closeButton
//        
//        //window!.rootViewController = navigationController
////        
////        let vc = UIViewController()
////        let navigation = UINavigationController(rootViewController: vc)
////        let hosting = UIHostingController(rootView: RouteDataView())
////        navigation.pushViewController(hosting, animated: false)
////        
////        window!.rootViewController = navigation
////        window!.makeKeyAndVisible()
//        //setNavigationBarBlur(navigationController)
//        
//        
//        
//        let closeButton2 = UIBarButtonItem(barButtonSystemItem: .close, target: self, action: #selector(closeButtonTapped))
//        let vc = RouteViewController()
//        let navigation = UINavigationController(rootViewController: vc)
//        vc.navigationItem.rightBarButtonItem = closeButton
//        let hosting = UIHostingController(rootView: SpecialPointDataView())
//        let hostNvc = UINavigationController(rootViewController: hosting)
//        hosting.navigationItem.rightBarButtonItem = closeButton2
//        
////        let closeButton2 = UIBarButtonItem(image: UIImage(systemName: "gear"), style: .done, target: nil, action: nil)
////        let vc = DashboardViewController()//RouteViewController()
////        let navigation = UINavigationController(rootViewController: vc)
////        vc.navigationItem.rightBarButtonItem = closeButton2
////        let hosting = UIHostingController(rootView: SpecialPointDataView())
////        let hostNvc = UINavigationController(rootViewController: hosting)
//        //hosting.navigationItem.rightBarButtonItem = closeButton2
//        
//        
//        
//        setNavigationBarBlur(navigation)
        

        //vc.present(hostNvc, animated: false)
        return true
    }
    
    func startAdmin() {
        self.adminCoordinator = AdminCoordinator()
        window!.rootViewController = self.adminCoordinator.navigationController
        window!.makeKeyAndVisible()
        self.adminCoordinator.startCoordinator()
    }
    
    func startClient() {
        self.clientCoordinator = ClientCoordinator()
        window!.rootViewController = self.clientCoordinator.navigationController
        window!.makeKeyAndVisible()
        clientCoordinator.startCoordinator()
    }
    
    func startDebugMenu() {
        let vc = DebugMenuViewController()
        let nvc = UINavigationController(rootViewController: vc)
        window!.rootViewController = nvc
        window!.makeKeyAndVisible()
    }
    
    func startSetPointFlow() {
        let setPointVC = SetPointViewController()
        setPointVC.didTapDoneWithPoint = {
            print($0)
        }
        window!.rootViewController = setPointVC
        window!.makeKeyAndVisible()
    }
    
    func startGetABAndShowRouteFlow() {
        let navigationController = UINavigationController()
        let mainViewController = UIViewController()
        mainViewController.view.backgroundColor = .red
        navigationController.setViewControllers([mainViewController], animated: false)
        self.setABCoordinator = SetABCoordinator(navigationController: navigationController)
        self.setABCoordinator.didFinishWithAB = { [weak self] value in
            let routeShowerViewController = RouteShowerViewController(
                aPoint: value.aPoint,
                bPoint: value.bPoint,
                avoidPoints: [ScenarioDataProvider.specialPoint2]
            )
            routeShowerViewController.didTapNextButtonWithJson = {
                json in
                guard let json = json else {
                    print("json is nil")
                    return
                }
                print(json)
            }
            navigationController.pushViewController(routeShowerViewController, animated: true)
        }
        self.setABCoordinator.startCoordinator()
        window!.rootViewController = navigationController
        window!.makeKeyAndVisible()
        setABCoordinator.startCoordinator()
    }
    
    @objc private func closeButtonTapped() {
        let alert = UIAlertController(title: "Error", message: "Impossible to build route", preferredStyle: .alert)
        alert.addAction(UIAlertAction(title: "OK", style: .default))
        window!.rootViewController?.present(alert, animated: true)
    }
    
    private func setNavigationBarBlur(_ navigationController: UINavigationController) {
        let appearance = UINavigationBarAppearance()
        appearance.configureWithTransparentBackground()
        appearance.backgroundEffect = UIBlurEffect(style: .light)
        
        navigationController.navigationBar.standardAppearance = appearance
        navigationController.navigationBar.scrollEdgeAppearance = appearance
    }
}
